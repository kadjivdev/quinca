<?php

namespace App\Http\Controllers\Revendeur;

use App\Http\Controllers\Controller;
use App\Models\Parametre\Depot;
use App\Models\Vente\{FactureClient, SessionCaisse, ReglementClient, RevendeurDepense};
use App\Models\Revendeur\FactureRevendeur;
use App\Models\Revendeur\LigneFactureRevendeur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class DepenseRevendeurController extends Controller
{
    /**
     * Affiche la liste des factures
     */

    public function index(Request $request)
    {
        try {
            Log::info('Début du chargement de la liste des factures');
            $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

            // User connecté
            $user = auth()->user();

            $day = $request->day ?? Carbon::now();
            // Chargement des factures avec les relations nécessaires
            if ($day) {
                $query = RevendeurDepense::with(['createdBy', 'validatedBy'])
                    ->whereDate("day", $day)
                    ->orderByDesc('id');
            } else {
                $query = RevendeurDepense::with(['createdBy', 'validatedBy'])
                    ->orderByDesc('id');
            }

            if (
                auth()->user()->hasRole("Super Administrateur")
                || auth()->user()->hasRole("CONTROLE INTERNE")
                || auth()->user()->hasRole("CONTROLE EXTERNE ET CELLULE DE REQUETE")
            ) {
                $depenses = $query->get();
                $depots = Depot::get();
            } else {
                $depenses = $query
                    ->where('created_by', $user->id)
                    ->get();
                $depots = $user->pointDeVente->depot;
            }
            // $depenses = $query
            //     ->where('created_by', $user->id)
            //     ->get();

            /**Montant total des ventes du jour */
            $totalVenteAmount = FactureRevendeur::whereNotNull("validated_by")
                ->whereDate("created_at", $day)
                ->where("point_de_vente_id", $user->point_de_vente_id)
                ->get()->sum(fn($facture) => ($facture->montant_ttc - $facture->montant_remise));

            /**Total des depenses du jour */
            $totalDepenses = $depenses->sum("amount");

            /** Calcul de la recette du jour */
            $recetteTotale = $totalVenteAmount - $totalDepenses;

            return view('pages.revendeur.depense.index', compact(
                'depenses',
                'depots',
                'date',
                'day',
                'totalVenteAmount',
                'totalDepenses',
                'recetteTotale'
            ));
        } catch (Exception $e) {
            Log::error('Erreur lors du chargement de la liste des depenses', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Une erreur est survenue lors du chargement des depenses');
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Début création facture Revendeur', ['request' => $request->all()]);

            // Validation
            $validated = $request->validate([
                'day' => 'required|date',
                'depot_id' => 'required|exists:depots,id',
                'amount' => 'required',
                'observation' => "nullable"
            ]);

            DB::beginTransaction();

            try {
                // Création de la depense
                RevendeurDepense::create($validated);

                DB::commit();

                return back()
                    ->with("success", 'Dépense inserée avec succès');
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Erreur création dépense', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->with("error", 'Erreur création dépense: ' . $e->getMessage());
        }
    }

    public function show(Request $request, $id)
    {
        try {
            Log::info('Début du chargement des détails de la facture revendeur ...', ['facture_id' => $id]);

            $facture = FactureRevendeur::with([
                'client',
                'lignes.article',
                'lignes.uniteVente',
                'lignes.facturedepot',
                // 'sessionCaisse',
                'createdBy',
                'pointDeVente'
            ])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'facture' => $facture,
                    'dateFacture' => $facture->date_facture->format('d/m/Y'),
                    'dateEcheance' => $facture->date_echeance->format('d/m/Y'),
                    'montantHT' => number_format($facture->montant_ht, 0, ',', ' '),
                    'montantTVA' => number_format($facture->montant_tva, 0, ',', ' '),
                    'montantTTC' => number_format($facture->montant_ttc, 0, ',', ' '),
                    'montantRegle' => number_format($facture->montant_regle, 0, ',', ' '),
                    'montantRestant' => number_format($facture->montant_ttc - $facture->montant_regle, 0, ',', ' '),
                    'tauxTVA' => $facture->taux_tva,
                    'tauxAIB' => $facture->taux_aib
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Erreur lors du chargement des détails de la facture', [
                'facture_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors du chargement des détails de la facture ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info("Total de ligne en debut de requete ", ["lignes" => $request->lignes]);
            Log::info('Début mise à jour facture', ['request' => $request->all(), 'facture_id' => $id]);

            $facture = FactureRevendeur::findOrFail($id);

            // Validation
            $validator = Validator::make($request->all(), [
                'date_facture' => 'required|date',
                'client_id' => 'required|exists:clients,id',
                'date_echeance' => 'date',
                'montant_regle' => 'required|numeric|min:0',
                'moyen_reglement' => 'required|string',

                'lignes' => 'required|array|min:1',
                'lignes*article_id' => 'required|exists,articles',
                'lignes*depot_id' => 'required|exits,depots',
                'lignes*quantite' => 'required',
                'lignes*tarification_id' => 'required',

                'type_facture' => 'required|in:simple,normaliser',
                'observations' => 'nullable|string',
                'moyen_reglement' => "required|in:espece,cheque,virement,carte_bancaire,MoMo,Flooz,Celtis_Pay,Effet,Avoir]",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Mise à jour de la facture
                $facture->update([
                    'date_facture' => Carbon::parse($request->date_facture)->startOfDay(),
                    'client_id' => $request->client_id,
                    'date_echeance' => Carbon::parse($request->date_echeance)->startOfDay(),
                    // 'session_caisse_id' => $sessionCaisse->id,
                    'created_by' => auth()->id(),
                    'observations' => $request->observations,
                    'statut' => 'brouillon',
                    'type_facture' => $request->type_facture === "normaliser" ? "NORMALISE" : "SIMPLE",
                    'moyen_reglement' => $request->moyen_reglement ?? $facture->moyen_reglement,
                    'montant_ht' => 0,
                    'montant_remise' => 0,
                    'montant_tva' => 0,
                    'montant_aib' => 0,
                    'montant_ttc' => 0,
                    'taux_tva' => 0.18,
                    'taux_aib' => 0.01,
                    'taux_remise' => 0,
                    'montant_ht_apres_remise' => 0,
                    'montant_tva' => 0,
                    'montant_aib' => 0,
                    'montant_ttc' => 0,
                    'montant_regle' => 0,
                    'montant_regle' => $request->montant_regle
                ]);

                Log::info("Total de ligne avant suppression des anciennes ", ["count" => count($request->lignes)]);

                // Suppression des anciens règlements
                $facture->reglements()->delete();

                // Réinitialisation des totaux et suppression des anciennes lignes
                $facture->lignes()->delete();

                // Création du règlement si nécessaire
                if ($request->montant_regle > 0) {
                    $reglement = new ReglementClient([
                        'facture_client_id' => $facture->id,
                        'date_reglement' => Carbon::parse($request->date_facture),
                        'type_reglement' => $request->moyen_reglement,
                        'montant' => $request->montant_regle,
                        'statut' => 'brouillon',
                        // 'session_caisse_id' => $sessionCaisse->id,
                        'created_by' => auth()->id(),
                    ]);
                    $facture->reglements()->save($reglement);
                }

                Log::info("Total de ligne après suppression des anciennes", ["count" => count($request->lignes)]);

                // Mise à jour des lignes
                foreach ($request->lignes as $index => $ligne) {
                    $ligneMontantTTC = $ligne['quantite'] * $ligne['tarification_id'];
                    $ligneMontantHt = $ligneMontantTTC / 1.19;
                    $ligneMontantTVA = $ligneMontantHt * 0.18;
                    $ligneMontantAIB = $ligneMontantHt * 0.01;
                    $ligneMontantRemise = $ligneMontantTTC * $ligne['taux_remise'] / 100;

                    Log::info("La ligne $index", [
                        "montantHt" => $ligneMontantHt,
                        "montantTva" => $ligneMontantTVA,
                        "montantAib" => $ligneMontantAIB,
                        "montantRemise" => $ligneMontantRemise,
                    ]);

                    $ligneFacture = new LigneFactureRevendeur([
                        'article_id' => $ligne['article_id'],
                        'unite_vente_id' => $ligne['unite_vente_id'],
                        'quantite' => $ligne['quantite'],
                        'depot' => $ligne['depot_id'],
                        'prix_unitaire_ht' => $ligne['tarification_id'],
                        'taux_remise' => $ligne['taux_remise'] ?? 0,
                        'taux_tva' => $request->type_facture === 'simple' ? 0 : 18 / 100,
                        'taux_aib' => $request->type_facture === 'simple' ? 0 : 1 / 100,
                        'montant_ht' => $ligneMontantHt,
                        'montant_tva' => $ligneMontantTVA,
                        'montant_aib' => $ligneMontantAIB,
                        'montant_ttc' => $ligneMontantTTC,
                        'montant_remise' => $ligneMontantRemise,
                        'montant_ht_apres_remise' => $ligneMontantHt - $ligneMontantRemise,
                        'quantite_livree' => 0,
                    ]);

                    $facture->lignes()->save($ligneFacture);
                }

                // Mise à jour des totaux
                $facture->update([
                    'montant_ht' => $facture->lignes->sum("montant_ht"),
                    'montant_remise' => $facture->lignes->sum("montant_remise"),
                    'montant_ht_apres_remise' => $facture->lignes->sum("montant_ht_apres_remise"),
                    'montant_tva' => $facture->lignes->sum("montant_tva"),
                    'montant_aib' => $facture->lignes->sum("montant_aib"),
                    'montant_ttc' => $facture->lignes->sum("montant_ttc"),
                    'montant_regle' => $request->montant_regle,
                ]);

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Facture mise à jour avec succès',
                    'data' => ['facture' => $facture->load([
                        'client',
                        'lignes.article',
                        'lignes.uniteVente',
                    ])]
                ]);
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Erreur mise à jour facture', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur mise à jour facture: ' . $e->getMessage()
            ], 500);
        }
    }

    public function validateFacture($id)
    {
        try {
            DB::beginTransaction();

            $facture = FactureRevendeur::findOrFail($id);

            if ($facture->statut === 'validee') {
                throw new Exception('Facture déjà validée');
            }

            $updateData = [
                'date_validation' => now(),
                'validated_by' => auth()->id(),
                'statut' => 'validee'
            ];

            $facture->update($updateData);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Facture validée',
                'data' => ['facture' => $facture->fresh(['client', 'createdBy'])]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur validation facture', [
                'facture_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $depense = RevendeurDepense::findOrFail($id);

            // Vérifier le statut
            if (!$depense) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'La depense n\'existe pas'
                ], 422);
            }

            // Supprimer la facture
            $depense->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Dépense supprimée avec succès'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression dépense', [
                'facture_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    public function details(FactureRevendeur $facture)
    {
        return response()->json([
            'id' => $facture->id,
            'numero' => $facture->numero,
            'client' => [
                'id' => $facture->client->id,
                'raison_sociale' => $facture->client->raison_sociale
            ],
            'montant_ttc' => $facture->montant_ttc,
            'montant_ttc' => $facture->montant_ttc,
            'montant_regle' => $facture->montant_regle,
            'reste_a_payer' => $facture->reste_a_payer,
            'date_facture' => $facture->date_facture->format('Y-m-d'),
            'statut' => $facture->statut
        ]);
    }

    /**
     * 
     */
    public function print(Request $request, FactureRevendeur $facture)
    {
        // Chargement des relations nécessaires
        $facture->load([
            'client',
            'lignes.article',
            'lignes.uniteVente',
            'createdBy',
            'validatedBy'
        ]);

        $logo = $request->get("logo");

        $pdf = PDF::loadView('pages.ventes.facture.partials.print-facture', compact('facture', "logo"));
        $pdf->setPaper('a4');

        return $pdf->stream("facture_{$facture->numero}.pdf");
    }

    /**
     * Bon à livrer
     */
    public function bonALivrer(Request $request, FactureRevendeur $facture)
    {
        // Chargement des relations nécessaires
        $facture->load([
            'client',
            'lignes.article',
            'lignes.uniteVente',
            'createdBy',
            'validatedBy'
        ]);

        $entete = $request->get("entete");

        $pdf = PDF::loadView('pages.ventes.facture.partials.bon-a-livrer', compact('facture', 'entete'));
        $pdf->setPaper('a4');

        return $pdf->stream("bon_a_livrer_{$facture->numero}.pdf");
    }

    /**
     * Bordereau de livraison
     */
    public function bordereauLivraison(Request $request, FactureRevendeur $facture)
    {
        // Chargement des relations nécessaires
        $facture->load([
            'client',
            'lignes.article',
            'lignes.uniteVente',
            'createdBy',
            'validatedBy'
        ]);

        $entete = $request->get("entete");

        $pdf = PDF::loadView('pages.ventes.facture.partials.bordereau-livraison', compact('facture', 'entete'));
        $pdf->setPaper('a4');

        return $pdf->stream("bordereau_{$facture->numero}.pdf");
    }


    public function MakevalidationDaily(Request $request)
    {
        $request->validate([
            'date_debut' => 'nullable|date',
            'client_id' => 'required|exists:clients,id',
            'moyen_paiement' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            $dateDebut = $request->get('date_debut', Carbon::now()->startOfMonth()->format('Y-m-d'));

            $factures = FactureRevendeur::whereBetween('date_facture', [$dateDebut, $dateDebut])
                ->where('type_vente', 'normale')
                ->where('encaisse', 'non')
                ->where('statut', 'validee')
                ->with('client')
                ->get();

            // Calcul de la somme des montants_ttc
            $sommeMontantTTCC = $factures->sum('montant_ttc');

            $facturesNonReglees = FactureClient::where('statut', 'validee')
                ->whereColumn('montant_regle', '<', 'montant_ttc') // montant réglé < montant total
                ->where('client_id', $request->client_id)
                ->orderBy('date_facture', 'asc') // Trier par date
                ->get();

            if (count($facturesNonReglees) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune facture en attente de règlement',
                ]);
            }

            foreach ($facturesNonReglees as $facture) {
                // Calculer le montant restant à régler pour cette facture
                $montantRestant = $facture->montant_ttc - $facture->montant_regle;

                if ($sommeMontantTTCC <= 0) {
                    break; // On arrête si la somme totale à régler est épuisée
                }

                // Le montant à régler pour cette facture est soit le montant restant, soit ce qui reste de la somme totale
                $montantARegler = min($montantRestant, $sommeMontantTTCC);

                $reglement = new ReglementClient();
                $reglement->facture_client_id = $facture->id;
                $reglement->facture()->associate($facture); // Important: associer la facture
                $reglement->date_reglement = now();
                $reglement->type_reglement = $request->type_reglement;
                $reglement->montant = $montantARegler;
                $reglement->created_by = auth()->id();
                $reglement->statut = ReglementClient::STATUT_BROUILLON;
                $reglement->save();

                // Vérifier si on a une session de caisse ouverte
                $sessionCaisse = SessionCaisse::where('utilisateur_id', auth()->id())
                    ->where('statut', 'ouverte')
                    ->first();

                if (!$sessionCaisse) {
                    throw new Exception('Vous devez avoir une session de caisse ouverte pour valider un règlement');
                }

                // Valider le règlement
                if (!$reglement->valider(auth()->id())) {
                    throw new Exception("Erreur lors de la validation du règlement");
                }

                // Mettre à jour la session caisse
                if (method_exists($sessionCaisse, 'mettreAJourTotaux')) {
                    $sessionCaisse->mettreAJourTotaux();
                }

                // Mettre à jour le montant réglé de la facture
                $facture->montant_regle += $montantARegler;
                $facture->save();

                // Réduire la somme totale à régler
                $sommeMontantTTCC -= $montantARegler;
            }

            foreach ($factures as $facture) {
                $facture->encaisse = 'oui';
                $facture->encaissed_at = now();
                $facture->save();
            }

            DB::commit();

            // Log de l'action
            Log::info('Règlement journalier revendeur avec succès', [
                'date' => $request->date_debut,
                'utilisateur_id' => auth()->id(),
                // 'session_caisse_id' => $sessionCaisse->id,
                'montant' => $sommeMontantTTCC
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Validation effectuée avec succès',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la validation du règlement journalier revendeur', [
                'date' => $request->date_debut,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
