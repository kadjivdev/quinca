<?php

namespace App\Http\Controllers\Achat;

use App\Models\Catalogue\Article;
use App\Models\Parametre\UniteMesure;
use App\Models\Achat\ProgrammationAchat;
use App\Models\Achat\LigneProgrammationAchat;
use App\Http\Controllers\Controller;
use App\Models\Stock\StockDepot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProgrammationAchatController extends Controller
{
    /**
     * Affiche la liste des programmations
     */
    public function index()
    {
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        // les dépôts de ce user
        $depots = auth()->user()->pointDeVente->depot->map(function ($depot) {
            $depot->articles = $depot->articles->transform(function ($article) use ($depot) {
                $article->reste = $article->reste($depot->id);
                $article->qteVendue = $article->ventes()->sum("quantite");
                return $article;
            });
            return $depot;
        });

        $data = [
            'date' => $date,
            'programmations' => ProgrammationAchat::with(['pointVente', 'fournisseur', 'lignes.article', 'lignes.uniteMesure'])
                ->latest()
                ->paginate(12),
            'totalProgrammations' => ProgrammationAchat::count(),
            'programmationsEnAttente' => ProgrammationAchat::whereNull('validated_at')->count(),
            'programmationsEnCours' => ProgrammationAchat::whereNull('validated_at')
                ->where('created_at', '<', now()->subDays(2))->count(),
            'programmationsValidees' => ProgrammationAchat::whereNotNull('validated_at')->count(),
            'fournisseurs' => \App\Models\Achat\Fournisseur::all(),
            'pointVentes' => \App\Models\Parametre\PointDeVente::all(),
            'articles' => Article::with(["stocks", "depots"])->where('statut', Article::STATUT_ACTIF)
                ->orderBy('designation')
                ->get(),

            'unitesMesure' => UniteMesure::where('statut', UniteMesure::STATUT_ACTIF)
                ->orderBy('libelle_unite')
                ->get(),

            "depots" => $depots
        ];

        return view('pages.achat.programmation.index', $data);
    }
    /**
     * Enregistre une nouvelle programmation
     */

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Récupérer le point de vente de l'utilisateur connecté
            $user = auth()->user();
            if (!$user->point_de_vente_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun point de vente associé à votre compte'
                ], 400);
            }

            // Validation de base
            $validated = $request->validate([
                'code' => 'required|unique:programmation_achats,code',
                'date_programmation' => 'required|date',
                'fournisseur_id' => 'required|exists:fournisseurs,id',
                'commentaire' => 'nullable|string',
                // 'depot' => 'required'
            ]);

            // Validation séparée pour les tableaux
            $request->validate([
                'articles' => 'required|array|min:1',
                'articles.*' => 'required|exists:articles,id',
                'quantites' => 'required|array|min:1',
                'quantites.*' => 'required|numeric|min:0.01',
                'unites' => 'required|array|min:1',
                'unites.*' => 'required|exists:unite_mesures,id'
            ]);

            // Création de la programmation avec le point de vente de l'utilisateur
            $programmation = ProgrammationAchat::create([
                'code' => $validated['code'],
                'date_programmation' => Carbon::parse($validated['date_programmation'])->startOfDay(),
                'point_de_vente_id' => $user->point_de_vente_id, // Point de vente automatique
                'fournisseur_id' => $validated['fournisseur_id'],
                'commentaire' => $validated['commentaire'] ?? null,
                // 'depot' => $request->depot,
            ]);

            // Création des lignes
            foreach ($request->articles as $index => $articleId) {
                LigneProgrammationAchat::create([
                    'programmation_id' => $programmation->id,
                    'article_id' => $articleId,
                    'unite_mesure_id' => $request->unites[$index],
                    'quantite' => $request->quantites[$index],
                    // 'depot' => $request->depot,
                ]);

            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Programmation créée avec succès',
                'data' => $programmation
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(). 'Erreur lors de la création de la programmation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les données pour l'édition
     */
    public function edit(ProgrammationAchat $programmation)
    {
        $programmation->load(['pointVente', 'fournisseur', 'lignes.article', 'lignes.uniteMesure']);

        return response()->json([
            'success' => true,
            'data' => $programmation
        ]);
    }

    /**
     * Met à jour une programmation
     */
    public function update(Request $request, ProgrammationAchat $programmation)
    {
        try {
            DB::beginTransaction();

            // Validation similaire au store
            $validated = $request->validate([
                'date_programmation' => 'required|date',
                // 'point_de_vente_id' => 'required|exists:point_de_ventes,id',
                'fournisseur_id' => 'required|exists:fournisseurs,id',
                'commentaire' => 'nullable|string',
                'articles' => 'required|array|min:1',
                'articles.*' => 'required|exists:articles,id',
                'quantites' => 'required|array|min:1',
                'quantites.*' => 'required|numeric|min:0.01',
                'unites' => 'required|array|min:1',
                'unites.*' => 'required|exists:unite_mesures,id'
            ]);

            // Mise à jour de la programmation
            $programmation->update($validated);

            // Suppression des anciennes lignes
            $programmation->lignes()->delete();

            // Création des nouvelles lignes
            foreach ($validated['articles'] as $index => $articleId) {
                LigneProgrammationAchat::create([
                    'programmation_id' => $programmation->id,
                    'article_id' => $articleId,
                    'unite_mesure_id' => $validated['unites'][$index],
                    'quantite' => $validated['quantites'][$index]
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Programmation mise à jour avec succès',
                'data' => $programmation
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime une programmation
     */
    public function destroy(ProgrammationAchat $programmation)
    {
        try {
            DB::beginTransaction();

            $programmation->lignes()->delete();
            $programmation->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Programmation supprimée avec succès'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valide une programmation
     */
    public function validated(ProgrammationAchat $programmation)
    {
        try {
            if ($programmation->validate()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Programmation validée avec succès'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Impossible de valider la programmation'
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Génère un nouveau code
     */
    /**
     * Génère un nouveau code de programmation unique
     * Format : DATE-USR-XXXXX
     * Exemple : 20241217-JDO-7YK9X
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateCode()
    {
        try {
            // Obtenir la date actuelle au format YYYYMMDD
            $date = now()->format('Ymd');

            // Obtenir les 3 premières lettres du nom d'utilisateur
            $user = auth()->user();
            $userInitials = substr(strtoupper($user->name), 0, 3);

            // Générer un UUID unique
            do {
                // Génération d'un UUID
                $uuid = \Illuminate\Support\Str::uuid();

                // Créer un hash unique basé sur l'UUID, l'ID utilisateur et le timestamp
                $hash = md5($uuid . $user->id . time());

                // Prendre les 5 premiers caractères et les convertir en majuscules
                $uniqueId = strtoupper(substr($hash, 0, 5));

                // Construire le code final
                $newCode = "{$date}-{$userInitials}-{$uniqueId}";

                // Vérifier si le code existe déjà
                $exists = ProgrammationAchat::where('code', $newCode)->exists();
            } while ($exists); // Continuer si le code existe déjà

            return response()->json([
                'success' => true,
                'code' => $newCode
            ]);
        } catch (Exception $e) {
            Log::error('Erreur lors de la génération du code de programmation', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du code'
            ], 500);
        }
    }

    /**
     * Recherche de programmations
     */
    public function search(Request $request)
    {
        $term = $request->get('q');

        $programmations = ProgrammationAchat::with(['pointVente', 'fournisseur'])
            ->where('code', 'LIKE', "%{$term}%")
            ->orWhereHas('fournisseur', function ($query) use ($term) {
                $query->where('nom', 'LIKE', "%{$term}%");
            })
            ->orWhereHas('pointVente', function ($query) use ($term) {
                $query->where('nom', 'LIKE', "%{$term}%");
            })
            ->take(10)
            ->get();

        return response()->json($programmations);
    }

    /**
     * Récupère les articles par fournisseur
     */
    public function getArticlesByFournisseur($fournisseurId)
    {
        $articles = Article::with('unites')
            ->where('fournisseur_id', $fournisseurId)
            ->get();

        return response()->json($articles);
    }

    /**
     * Import de programmations
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            // Logique d'import à implémenter
            return response()->json([
                'success' => true,
                'message' => 'Import réussi'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'import: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les détails d'une programmation pour le bon de commande
     */
    public function show($id)
    {
        try {
            $programmation = ProgrammationAchat::with([
                'pointVente',
                'fournisseur',
                'lignes.article',
                'lignes.uniteMesure'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'programmation' => [
                        'code' => $programmation->code,
                        'point_vente' => [
                            'nom' => $programmation->pointVente->nom_pv
                        ],
                        'fournisseur' => [
                            'nom' => $programmation->fournisseur->raison_sociale
                        ],
                        'validated_at' => $programmation->validated_at ? $programmation->validated_at->format('d/m/Y') : null
                    ],
                    'articles' => $programmation->lignes->map(function ($ligne) {
                        return [
                            'id' => $ligne->article->id,
                            'reference' => $ligne->article->code_article,
                            'designation' => $ligne->article->designation,
                            'unite' => $ligne->uniteMesure->libelle_unite,
                            'quantite' => $ligne->quantite,
                            'prix_unitaire' => $ligne->article->prix_unitaire ?? 0
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails: ' . $e->getMessage()
            ], 404);
        }
    }

    public function rejectProgrammation(Request $request, $id)
    {
        $programmation = ProgrammationAchat::findorFail($id);

        $request->validate([
            'motif_rejet' => 'required|string'
        ]);

        $programmation->motif_rejet = $request->motif_rejet;
        $programmation->rejected_by = Auth::id();
        $programmation->rejected_at = now();

        $programmation->save();

        return response()->json([
            'success' => true,
            'message' => 'Programmation rejetée avec succès',
            'data' => $programmation
        ]);
    }

    public function validees()
    {
        $user = auth()->user();
        $programmationsValidees = ProgrammationAchat::whereNotNull('validated_at')
            ->where('point_de_vente_id', $user->point_de_vente_id)
            ->with(['pointVente', 'fournisseur'])
            ->orderBy('validated_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $programmationsValidees
        ]);
    }
}
