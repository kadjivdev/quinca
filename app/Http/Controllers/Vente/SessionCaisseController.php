<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;

use App\Models\Vente\SessionCaisse;
use App\Models\Parametre\Caisse;
use App\Models\Vente\FactureClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SessionCaisseController extends Controller
{
    /**
     * Affiche la liste des sessions de caisse.
     */
    public function index()
    {
        // Récupérer les sessions avec la pagination
        $sessions = SessionCaisse::with(['factures', 'caisse', 'utilisateur'])
            ->orderBy('date_ouverture', 'desc')
            // ->paginate(10);
            ->get();

        // Récupérer l'utilisateur connecté
        $user = auth()->user();

        $caisses = Caisse::All();

        // Vérifier si l'utilisateur a une session ouverte
        $hasSessionOuverte = SessionCaisse::where('utilisateur_id', $user->id)
            ->where('statut', 'ouverte')
            ->exists();

        // Calculer les statistiques
        $totalEncaissements = SessionCaisse::whereMonth('date_ouverture', now()->month)
            ->sum('total_encaissements');

        $ecartMoyen = SessionCaisse::whereMonth('date_ouverture', now()->month)
            ->where('statut', 'fermee')
            ->avg('ecart') ?? 0;

        // Formater la date
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        return view('pages.ventes.session.index', compact(
            'sessions',
            'caisses',
            'hasSessionOuverte',
            'totalEncaissements',
            'ecartMoyen',
            'date'
        ));
    }

    /**
     * Ouvre une nouvelle session de caisse.
     */

    public function store(Request $request)
    {
        try {
            Log::info('Tentative d\'ouverture de session de caisse', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name
            ]);

            // Validation des données avec coordonnées obligatoires
            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'observations' => 'nullable|string|max:500',
                'montant_ouverture' => 'nullable|numeric|min:0',
            ]);

            // Récupérer l'utilisateur connecté
            $user = auth()->user();

            // Vérifier le point de vente
            if (!$user->point_de_vente_id) {
                Log::error('Point de vente non trouvé', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes associé à aucun point de vente.'
                ], 422);
            }

            // Vérifier session existante
            $sessionUtilisateur = SessionCaisse::where('utilisateur_id', $user->id)
                ->where('statut', 'ouverte')
                ->first();

            if ($sessionUtilisateur) {
                Log::warning('Session déjà ouverte', [
                    'user_id' => $user->id,
                    'session_id' => $sessionUtilisateur->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Vous avez déjà une session de caisse ouverte.'
                ], 422);
            }

            // Récupérer la caisse active
            $caisse = Caisse::where('point_de_vente_id', $user->point_de_vente_id)
                ->where('actif', true)
                ->first();

            if (!$caisse) {
                Log::error('Aucune caisse active', [
                    'point_vente_id' => $user->point_de_vente_id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune caisse active n\'est disponible.'
                ], 422);
            }

            // Vérifier caisse disponible
            $sessionCaisse = SessionCaisse::where('caisse_id', $caisse->id)
                ->where('statut', 'ouverte')
                ->first();

            if ($sessionCaisse) {
                Log::warning('Caisse occupée', [
                    'caisse_id' => $caisse->id,
                    'session_id' => $sessionCaisse->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Cette caisse a déjà une session ouverte.'
                ], 422);
            }

            // Créer la session avec montant fixe et coordonnées
            DB::beginTransaction();
            try {
                $session = SessionCaisse::create([
                    'utilisateur_id' => $user->id,
                    'caisse_id' => $caisse->id,
                    'date_ouverture' => now(),
                    'montant_ouverture' => $request->montant_ouverture,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'observations' => $request->observations,
                    'point_de_vente_id' => $user->point_de_vente_id,
                    'statut' => 'ouverte'
                ]);

                DB::commit();

                Log::info('Session créée', [
                    'session_id' => $session->id,
                    'lat' => $request->latitude,
                    'lng' => $request->longitude
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Session de caisse ouverte avec succès',
                    'redirect' => route('vente.sessions.index')
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Erreur création session', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ouverture de la session : ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Affiche les détails d'une session
     */
    public function show(SessionCaisse $session)
    {
        $session->load(['factures', 'detailsComptage', 'utilisateur']);

        return view('pages.ventes.session.show', compact('session'));
    }

    /**
     * Ferme une session de caisse
     */
    /**
     * Ferme une session de caisse
     */

    public function fermer(Request $request, $id)
    {
        try {
            // Récupération explicite de la session
            $session = SessionCaisse::findOrFail($id);

            Log::info('Données de session au début de fermer()', [
                'session_id' => $session->id,
                'statut' => $session->statut,
                'exists' => $session->exists
            ]);

            // Validation simplifiée
            $validated = $request->validate([
                'montant_fermeture' => 'required|numeric|min:0',
                'observations_fermeture' => 'nullable|string|max:500',
            ]);

            // Vérification explicite du statut
            if ($session->statut !== 'ouverte') {
                Log::warning('Session non ouverte détectée', [
                    'session_id' => $session->id,
                    'statut' => $session->statut
                ]);
                return response()->json([
                    'success' => false,
                    'message' => "Cette session n'est pas ouverte. Statut actuel: " . $session->statut
                ], 422);
            }

            DB::beginTransaction();
            try {
                // Mise à jour de la session simplifiée
                DB::table('session_caisses')
                    ->where('id', $session->id)
                    ->update([
                        'date_fermeture' => now(),
                        'montant_fermeture' => $validated['montant_fermeture'],
                        'observations_fermeture' => $validated['observations_fermeture'],
                        'statut' => 'fermee',
                        'updated_at' => now()
                    ]);

                DB::commit();

                Log::info('Session fermée avec succès', [
                    'session_id' => $session->id,
                    'montant_fermeture' => $validated['montant_fermeture']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Session fermée avec succès',
                    'redirect' => route('vente.sessions.index')
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la fermeture de la session', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la fermeture de la session : ' . $e->getMessage()
            ], 500);
        }
    }

    // Ajout d'une méthode de debug


    public function debugSession(SessionCaisse $session)
    {
        return response()->json([
            'session_id' => $session->id,
            'statut_brut' => $session->statut,
            'statut_lowercase' => strtolower($session->statut),
            'est_ouverte' => $session->estOuverte(),
            'utilisateur_id' => $session->utilisateur_id,
            'utilisateur_courant' => auth()->id(),
            'raw_attributes' => $session->getAttributes()
        ]);
    }

    public function ventesBySession($sessionId)
    {
        $ventes = FactureClient::where('session_caisse_id', $sessionId)
            ->where('montant_regle', '>', 0)
            ->with(['sessionCaisse.utilisateur', 'client'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        $ventes->transform(function ($facture) {
            // Calcul du reste à payer
            $facture->reste_a_payer = $facture->montant_ttc - $facture->montant_regle;

            // Détermination du vrai statut basé sur le paiement
            if ($facture->statut === 'brouillon') {
                $facture->statut_reel = 'brouillon';
            } elseif ($facture->statut === 'validee') {
                if ($facture->montant_regle == 0) {
                    $facture->statut_reel = 'validee';
                } elseif ($facture->montant_regle < $facture->montant_ttc) {
                    $facture->statut_reel = 'partiellement_payee';
                } elseif ($facture->montant_regle >= $facture->montant_ttc) {
                    $facture->statut_reel = 'payee';
                }
            }

            // Vérifier si la facture est en retard
            $facture->is_overdue = $facture->statut !== 'payee'
                && Carbon::now()->startOfDay()->gt($facture->date_echeance);

            return $facture;
        });

        $non_encaisses = $ventes->filter(function ($vente) {
            return is_null($vente->encaissed_at);
        });
        $ttc_non_encaisses = $non_encaisses->sum('montant_regle');

        $encaisses = $ventes->filter(function ($vente) {
            return !is_null($vente->encaissed_at);
        });
        $ttc_encaisses = $encaisses->sum('montant_regle');

        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        // dd($ventes);
        return view('pages.ventes.encaissement.index', compact(['ventes', 'date', 'non_encaisses', 'encaisses', 'ttc_non_encaisses', 'ttc_encaisses']));
    }

    public function encaisser(Request $request, FactureClient $facture)
    {
        $facture->encaissed_by = Auth::id();
        $facture->encaissed_at = now();
        $facture->reference_recu = $request->reference_recu;

        $facture->save();
        return response()->json([
            'success' => true,
            'message' => 'Facture encaissée avec succès',
            'data' => $facture
        ]);
    }

    /**
     * Génère le rapport de la session
     */
    // public function rapport(SessionCaisse $session)
    // {
    //     $session->load(['factures', 'detailsComptage', 'utilisateur']);

    //     $pdf = PDF::loadView('pages.caisse.sessions.rapport', compact('session'));

    //     return $pdf->download('rapport_session_' . $session->id . '.pdf');
    // }
}
