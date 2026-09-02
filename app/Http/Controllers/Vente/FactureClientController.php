<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use App\Models\Vente\Client;
use App\Models\Catalogue\{Article};
use App\Models\Parametre\Agent;
use App\Models\Parametre\ConversionUnite;
use App\Models\Parametre\Depot;
use App\Models\Parametre\PointDeVente;
use App\Models\Vente\{FactureClient, LigneFacture, PointVente, SessionCaisse, ReglementClient};
use App\Models\Parametre\Societe;
use App\Models\Parametre\UniteMesure;
use App\Models\Stock\StockDepot;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ServiceStockEntree;
use Illuminate\Support\Facades\Auth;

class FactureClientController extends Controller
{
    /**
     * Affiche la liste des factures
     */

    private $serviceStockEntree;

    public function __construct(ServiceStockEntree $serviceStockEntree)
    {
        $this->serviceStockEntree = $serviceStockEntree;
    }

    public function index(Request $request)
    {
        try {
            $pointsVentes = PointVente::all();

            Log::info('Début du chargement de la liste des factures');
            $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');
            $configuration = Societe::first();
            $tauxTva = $configuration ? $configuration->taux_tva : 18;

            // Construction de la requête de base
            if ($request->debut && $request->fin) {
                $query = FactureClient::with(['client', 'createdBy','lignes'])
                    ->orderBy('created_at', 'desc')
                    ->whereBetween('created_at', [Carbon::parse($request->debut)->startOfDay(), Carbon::parse($request->fin)->endOfDay()]);
            } else {
                $query = FactureClient::with(['client', 'createdBy','lignes'])
                    ->orderBy('created_at', 'desc')
                    ->whereBetween('created_at', [Carbon::parse(now())->startOfMonth(), Carbon::parse(now())->endOfMonth()]);
            }

            // Chargement des factures avec les relations nécessaires
            if ($request->point_vente_id && !$request->client_id) {
                $factures = $query->get()
                    ->filter(function ($facture) use ($request) {
                        return $facture->createdBy->point_de_vente_id == $request->point_vente_id;
                    });
            } elseif ($request->client_id && !$request->point_vente_id) {
                $factures = $query->get()
                    ->filter(function ($facture) use ($request) {
                        return $facture->client_id == $request->client_id;
                    });
            } elseif ($request->client_id && $request->point_vente_id) {
                $factures = $query->get()
                    ->filter(function ($facture) use ($request) {
                        return ($facture->createdBy->point_de_vente_id == $request->point_vente_id && $facture->client_id == $request->client_id);
                    });
            } else {
                $factures = $query->get();
            }

            if ($request->zone_id) {
                $factures = $factures->filter(function ($facture) use ($request) {
                    return $facture->client?->zone_id == $request->zone_id;
                });
            }

            if ($request->depot_id) {
                $factures = $query->get()
                    ->filter(function ($facture) use ($request) {
                        return ($facture->createdBy->point_de_vente_id == $request->point_vente_id && $facture->client_id == $request->client_id);
                    });
            }

            if ($request->agent_id) {
                $factures = $factures->filter(function ($facture) use ($request) {
                    return $facture->client?->agent_id == $request->agent_id;
                });
            }

            // Ajouter des attributs calculés pour chaque facture
            $factures->transform(function ($facture) {
                // Calcul du reste à payer
                $facture->reste_a_payer = ($facture->montant_ttc - $facture->montant_remise) - $facture->montant_regle;

                // Détermination du vrai statut basé sur le paiement
                if ($facture->statut === 'brouillon') {
                    $facture->statut_reel = 'brouillon';
                } elseif ($facture->statut === 'validee') {
                    if ($facture->montant_regle == 0) {
                        $facture->statut_reel = 'validee';
                    } elseif ($facture->montant_regle < $facture->montant_ttc - $facture->montant_remise) {
                        $facture->statut_reel = 'partiellement_payee';
                    } elseif ($facture->montant_regle >= $facture->montant_ttc - $facture->montant_remise) {
                        $facture->statut_reel = 'payee';
                    }
                }

                /**
                 * Détermination de l'état de la facture basé sur la livraison et le paiement
                 */
                $facture->state = "";
                $facture->payement_state = "";
                if ($facture->montant_regle==0) {
                    $facture->payement_state = "non_payee";
                }

                if ($facture->montant_regle>0 && $facture->montant_regle<$facture->montant_ttc) {
                    $facture->payement_state = "partiellement_payee";
                }

                if ($facture->montant_regle>0 && $facture->montant_regle>=$facture->montant_ttc) {
                    $facture->payement_state = "payee";
                }

                $lignes = $facture->lignes;
                $quantitesLivrees = $lignes->map(function ($ligne) {
                    return (float) ($ligne->quantite_livree ?? 0);
                });
                $quantitesFacturees = $lignes->map(function ($ligne) {
                    return (float) $ligne->quantite;
                });

                if ($quantitesFacturees->isNotEmpty() && $quantitesLivrees->every(function ($quantiteLivree, $index) use ($quantitesFacturees) {
                    return $quantiteLivree >= $quantitesFacturees[$index];
                })) {
                    $facture->state = "livree";
                } elseif ($quantitesLivrees->contains(function ($quantiteLivree) {
                    return $quantiteLivree > 0;
                })) {
                    $facture->state = "partiellement_livree";
                } else {
                    $facture->state = "non_livree";
                }
                /** Fin de la determonation d'état des factures */

                // Vérifier si la facture est en retard
                $facture->is_overdue = $facture->statut !== 'payee'
                    && Carbon::now()->startOfDay()->gt($facture->date_echeance);

                return $facture;
            });

            $facturesResteAPayer = $factures->filter(function ($facture) {
                return $facture->reste_a_payer > 0;
            });
            $montantResteAPyer = $facturesResteAPayer->sum('montant_ttc') - $facturesResteAPayer->sum('montant_remise');

            // Calculer le montant total des factures du mois en cours
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            $facturesDuMois = $factures->filter(function ($facture) use ($currentMonth, $currentYear) {
                return Carbon::parse($facture->date_facture)->month == $currentMonth &&
                    Carbon::parse($facture->date_facture)->year == $currentYear;
            });

            $montantFactureMois = $facturesDuMois->sum('montant_ttc');

            // Calculer le total encaissé et le nombre de factures encaissées
            $facturesEncaissees = $facturesDuMois->filter(function ($facture) {
                return !is_null($facture->encaissed_at);
            });

            $totalEncaisse = $facturesEncaissees->sum('montant_ttc');
            $nombreEncaisse = $facturesEncaissees->count();

            $statsFactures = [
                'total_mois' => $montantFactureMois,
                'total_encaisse' => $totalEncaisse,
                'nombre_encaisse' => $nombreEncaisse,
                'montant_en_attente' => $montantResteAPyer,
                'factures_en_attente' => $facturesResteAPayer,
            ];

            Log::info('Liste des factures chargée avec succès', [
                'nombre_factures' => count($factures)
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'factures' => $factures
                    ]
                ]);
            }

            // Charger la liste des clients pour le filtre
            $clients = Client::where('point_de_vente_id', Auth()->user()->point_de_vente_id)->orderBy('raison_sociale')->get(['id', 'raison_sociale', 'taux_aib']);
            $zones = Zone::all();
            $depots = Depot::all();
            $agents = Agent::all();

            return view('pages.ventes.facture.index', compact('factures', 'clients', 'date', 'tauxTva', 'statsFactures', 'pointsVentes', 'zones', 'depots', 'agents'));
        } catch (Exception $e) {
            Log::error('Erreur lors du chargement de la liste des factures', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Une erreur est survenue lors du chargement des factures'
                ], 500);
            }

            return back()->with('error', 'Une erreur est survenue lors du chargement des factures');
        }
    }

    // les ventes à crédit
    public function credits(Request $request)
    {
        try {
            $pointsVentes = PointVente::all();

            Log::info('Début du chargement de la liste des factures');
            $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');
            $configuration = Societe::first();
            $tauxTva = $configuration ? $configuration->taux_tva : 18;

            // Construction de la requête de base
            $query = FactureClient::with(['client', 'createdBy'])
                ->whereNotNull("recommandeur_credit")
                ->orderBy('created_at', 'desc')
                ->whereBetween('created_at', [Carbon::parse($request->debut)->startOfDay(), Carbon::parse($request->fin)->endOfDay()]);

            // Chargement des factures avec les relations nécessaires
            if ($request->point_vente_id && !$request->client_id) {
                $factures = $query->get()
                    ->filter(function ($facture) use ($request) {
                        return $facture->createdBy->point_de_vente_id == $request->point_vente_id;
                    });
            } elseif ($request->client_id && !$request->point_vente_id) {
                $factures = $query->get()
                    ->filter(function ($facture) use ($request) {
                        return $facture->client_id == $request->client_id;
                    });
            } elseif ($request->client_id && $request->point_vente_id) {
                $factures = $query->get()
                    ->filter(function ($facture) use ($request) {
                        return ($facture->createdBy->point_de_vente_id == $request->point_vente_id && $facture->client_id == $request->client_id);
                    });
            } else {
                $factures = $query->get();
            }

            // dd($request->zone_id);
            if ($request->zone_id) {
                $factures = $factures->filter(function ($facture) use ($request) {
                    return $facture->client?->zone_id == $request->zone_id;
                });
            }

            // Ajouter des attributs calculés pour chaque facture
            $factures->transform(function ($facture) {
                // Calcul du reste à payer
                $facture->reste_a_payer = ($facture->montant_ttc - $facture->montant_remise) - $facture->montant_regle;

                // Détermination du vrai statut basé sur le paiement
                if ($facture->statut === 'brouillon') {
                    $facture->statut_reel = 'brouillon';
                } elseif ($facture->statut === 'validee') {
                    if ($facture->montant_regle == 0) {
                        $facture->statut_reel = 'validee';
                    } elseif ($facture->montant_regle < $facture->montant_ttc - $facture->montant_remise) {
                        $facture->statut_reel = 'partiellement_payee';
                    } elseif ($facture->montant_regle >= $facture->montant_ttc - $facture->montant_remise) {
                        $facture->statut_reel = 'payee';
                    }
                }

                // Vérifier si la facture est en retard
                $facture->is_overdue = $facture->statut !== 'payee'
                    && Carbon::now()->startOfDay()->gt($facture->date_echeance);

                return $facture;
            });

            $facturesResteAPayer = $factures->filter(function ($facture) {
                return $facture->reste_a_payer > 0;
            });
            $montantResteAPyer = $facturesResteAPayer->sum('montant_ttc') - $facturesResteAPayer->sum('montant_remise');

            // Calculer le montant total des factures du mois en cours
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            $facturesDuMois = $factures->filter(function ($facture) use ($currentMonth, $currentYear) {
                return Carbon::parse($facture->date_facture)->month == $currentMonth &&
                    Carbon::parse($facture->date_facture)->year == $currentYear;
            });

            $montantFactureMois = $facturesDuMois->sum('montant_ttc');

            // Calculer le total encaissé et le nombre de factures encaissées
            $facturesEncaissees = $facturesDuMois->filter(function ($facture) {
                return !is_null($facture->encaissed_at);
            });

            $totalEncaisse = $facturesEncaissees->sum('montant_ttc');
            $nombreEncaisse = $facturesEncaissees->count();

            $statsFactures = [
                'total_mois' => $montantFactureMois,
                'total_encaisse' => $totalEncaisse,
                'nombre_encaisse' => $nombreEncaisse,
                'montant_en_attente' => $montantResteAPyer,
                'factures_en_attente' => $facturesResteAPayer,
            ];

            Log::info('Liste des factures chargée avec succès', [
                'nombre_factures' => count($factures)
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'factures' => $factures
                    ]
                ]);
            }

            // Charger la liste des clients pour le filtre
            $clients = Client::where('point_de_vente_id', Auth()->user()->point_de_vente_id)->orderBy('raison_sociale')->get(['id', 'raison_sociale', 'taux_aib']);
            $zones = Zone::all();

            return view('pages.ventes.facture.venteCredits', compact('factures', 'clients', 'date', 'tauxTva', 'statsFactures', 'pointsVentes', 'zones'));
        } catch (Exception $e) {
            Log::error('Erreur lors du chargement de la liste des factures', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Une erreur est survenue lors du chargement des factures'
                ], 500);
            }

            return back()->with('error', 'Une erreur est survenue lors du chargement des factures');
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Début création facture', ['request' => $request->all()]);

            // Vérifications initiales
            $sessionCaisse = SessionCaisse::ouverte()
                ->where('point_de_vente_id', auth()->user()->point_de_vente_id)
                ->first();

            if (!$sessionCaisse) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Session de caisse requise.'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'date_facture' => 'required|date',
                'client_id' => 'required|exists:clients,id',
                'date_echeance' => 'date',
                'montant_regle' => 'required|numeric|min:0',

                'recommandeur_credit' => 'nullable|string',

                'lignes' => 'required|array|min:1',
                'lignes.*.article_id' => 'required|exists:articles,id',
                'lignes.*.depot_id' => 'required|exists:depots,id',
                'lignes.*.quantite' => 'required|numeric|min:0.01',
                'lignes.*.tarification_id' => 'required',

                'type_facture' => 'required|in:simple,normaliser',
                'observations' => 'nullable|string',
                'moyen_reglement' => 'required|in:espece,cheque,virement,carte_bancaire,MoMo,Flooz,Celtis_Pay,Effet,Avoir',
            ], [
                'date_facture.required' => 'La date de facture est obligatoire.',
                'date_facture.date' => 'La date de facture doit être une date valide.',

                'client_id.required' => 'Le client est obligatoire.',
                'client_id.exists' => "Le client sélectionné n'existe pas.",

                'date_echeance.date' => "La date d'échéance doit être une date valide.",

                'montant_regle.required' => 'Le montant réglé est obligatoire.',
                'montant_regle.numeric' => 'Le montant réglé doit être un nombre.',
                'montant_regle.min' => 'Le montant réglé ne peut pas être négatif.',

                'recommandeur_credit.string' => 'Le recommandeur crédit doit être une chaîne de caractères.',

                'lignes.required' => 'Au moins une ligne de facture est requise.',
                'lignes.array' => 'Les lignes doivent être un tableau.',
                'lignes.min' => 'Au moins une ligne de facture est requise.',

                'lignes.*.article_id.required' => 'L\'article est obligatoire pour chaque ligne.',
                'lignes.*.article_id.exists' => "L'article sélectionné n'existe pas.",

                'lignes.*.depot_id.required' => 'Le dépôt est obligatoire pour chaque ligne.',
                'lignes.*.depot_id.exists' => "Le dépôt sélectionné n'existe pas.",

                'lignes.*.quantite.required' => 'La quantité est obligatoire pour chaque ligne.',
                'lignes.*.quantite.numeric' => 'La quantité doit être un nombre.',
                'lignes.*.quantite.min' => 'La quantité doit être supérieure à 0.',

                'lignes.*.tarification_id.required' => 'La tarification est obligatoire pour chaque ligne.',

                'type_facture.required' => 'Le type de facture est obligatoire.',
                'type_facture.in' => 'Le type de facture doit être "simple" ou "normaliser".',

                'observations.string' => 'Les observations doivent être une chaîne de caractères.',

                'moyen_reglement.required' => 'Le moyen de règlement est obligatoire.',
                'moyen_reglement.in' => 'Le moyen de règlement sélectionné est invalide.',
            ]);

            if ($validator->fails()) {
                Log::debug("Erreure lors de l'enregistrement de la facture ", ["error" => $validator->errors()]);
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Credit file handling
            $preuve_credit = null;
            if ($request->hasFile("preuve_credit")) {
                $fileName = time() . '_' . $request->file('preuve_credit')->getClientOriginalName();

                $request->file('preuve_credit')->move("preuve_credit", $fileName);

                $preuve_credit = asset("/preuve_credit/" . $fileName);
            }

            /** 
             * Verification de l'unicité des articles dans les données envoyées
             * && verification des quantités disponibles dans la base de données
             */
            $selectedArticles = [];
            foreach ($request->lignes as $ligne) {
                $depot = Depot::find($ligne["depot_id"]);
                $article = Article::find($ligne['article_id']);

                /**
                 * on verifie si les articles selectionnés
                 * sont tous dans son depôts pour les non admins
                 */
                if (!auth()->user()->hasRole('Super Administrateur')) {
                    $userPv = auth()->user()->pointDeVente;
                    $userPv_depotIds = $userPv->depot->pluck("id")->toArray(); //les depots du users

                    if (!in_array($ligne["depot_id"], $userPv_depotIds)) {
                        return response()->json([
                            'status' => false,
                            'message' => "Le dépôt ($depot->libelle_depot) ne vous appartient pas! Vous ne pouvez pas y passer une ecriture "
                        ], 500);
                    }
                }

                /**
                 * on verifie si l'article a été choisi deux fois
                 */
                if (isset($selectedArticles[$ligne['article_id']])) {
                    return response()->json([
                        'status' => false,
                        'message' => "L'article ({$article->designation}) a été sélectionné plusieurs fois. Veuillez regrouper les quantités dans une seule ligne."
                    ], 422);
                }
                $selectedArticles[$ligne['article_id']] = true; //on conpte cet article comme déjà selectionné


                //On verifie si les quantités saisies au niveau des articles ne depasse pas le reste de quantité sur l'article
                $stock = StockDepot::where('depot_id', $ligne["depot_id"])
                    ->where('article_id', $ligne['article_id'])
                    ->first();

                /**
                 * Recherche de la conversion
                 */
                $venteUnite = UniteMesure::findOrFail($ligne['unite_vente_id']);
                $article = Article::findOrFail($ligne['article_id']);

                $conversion = $this->serviceStockEntree
                    ->rechercherConversion(
                        $ligne['unite_vente_id'],
                        $article->unite_mesure_id, // $stock->unite_mesure_id,
                        $stock->article_id
                    );

                if (!$conversion) {
                    return response()->json([
                        'status' => false,
                        'message' => "Il n'y a pas de conversion de l'unité ($venteUnite->libelle_unite) vers ({{$article->uniteMesure?->libelle_unite}}) pour l'article ($article->code_article), ni l'inverse! Veuillez créer cette conversion afin de continuer l'opération"
                    ], 500);
                }

                /**
                 * Obtention de la quantité convertie
                 */

                // stock existant en unité de base de l'article
                $resteStock = isset($ligne["depot_stock"]) ? $ligne["depot_stock"] : 0;

                // unite de vente vers unite de base de l'article
                $QteConvertie = $this->serviceStockEntree
                    ->convertirQuantite($ligne['quantite'], $conversion, $ligne['unite_vente_id']);

                Log::debug("Quantité convertie", [
                    "quantite_saisie" => $ligne['quantite'],
                    "quantite_convertie" => $QteConvertie,
                    "unite_vente" => $venteUnite->libelle_unite,
                    "unite_article" => $article->uniteMesure?->libelle_unite,
                ]);

                // on verifie la quantité restante de l'article dans le depot est suffisante
                if ($resteStock < $QteConvertie) {
                    return response()->json([
                        'status' => false,
                        'message' => "Le reste du stock de l'article ($article->designation) est de $resteStock {{$article->uniteMesure?->libelle_unite}} dans le depôt ({$stock->depot?->libelle_depot})! Vous avez saisi $QteConvertie {{$article->uniteMesure?->libelle_unite}}!  Stock insuiffisant par rapport à la quantité saisie"
                    ], 500);
                }
            }

            DB::beginTransaction();

            try {
                // Création de la facture
                $facture = new FactureClient();
                $facture->fill([
                    'date_facture' => Carbon::parse($request->date_facture)->startOfDay(),
                    'client_id' => $request->client_id,
                    'date_echeance' => Carbon::parse($request->date_echeance)->startOfDay(),
                    'session_caisse_id' => $sessionCaisse->id,
                    'created_by' => auth()->id(),
                    'observations' => $request->observations,
                    'statut' => 'brouillon',
                    'type_facture' => $request->type_facture === "normaliser" ? "NORMALISE" : "SIMPLE",
                    'moyen_reglement' => $request->moyen_reglement,
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

                    // credit
                    "preuve_credit" => $preuve_credit,
                    "recommandeur_credit" => $request->recommandeur_credit,
                ]);

                $facture->save();

                // Création des lignes
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

                    $ligneFacture = new LigneFacture([
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

                // Création du règlement si nécessaire
                if ($request->montant_regle > 0) {
                    $reglement = new ReglementClient([
                        'facture_client_id' => $facture->id,
                        'date_reglement' => Carbon::parse($request->date_facture),
                        'type_reglement' => $request->moyen_reglement,
                        'montant' => $request->montant_regle,
                        'statut' => 'brouillon',
                        'session_caisse_id' => $sessionCaisse->id,
                        'created_by' => auth()->id(),
                    ]);
                    $facture->reglements()->save($reglement);
                }

                // verification du depassement du plafond de crédit
                $montant = -$facture->client->solde() + $facture->montant_ttc;
                $plafondCredit = $facture->client?->plafond_credit;

                if ($plafondCredit > 0 && $montant > $plafondCredit) {
                    throw new Exception("Le montant $ {$facture->montant_ttc} de la vente, ajouté au solde $ {$facture->client->solde()} dépasse son plafond de crédit $ {$plafondCredit}");
                };

                $sessionCaisse->mettreAJourTotaux();
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Facture créée avec succès',
                    'data' => ['facture' => $facture->load([
                        'client',
                        'lignes.article',
                        'lignes.uniteVente',
                        'sessionCaisse',
                        'createdBy',
                        'reglements'
                    ])]
                ]);
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Erreur création facture', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ligne' => $e->getLine()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur création facture: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info("Total de ligne en debut de requete ", ["lignes" => $request->lignes]);
            Log::info('Début mise à jour facture', ['request' => $request->all(), 'facture_id' => $id]);

            // Vérifications initiales
            $sessionCaisse = SessionCaisse::ouverte()
                ->where('point_de_vente_id', auth()->user()->point_de_vente_id)
                ->first();

            if (!$sessionCaisse) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Session de caisse requise.'
                ], 422);
            }

            $facture = FactureClient::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'date_facture' => 'required|date',
                'client_id' => 'required|exists:clients,id',
                'date_echeance' => 'date',
                'montant_regle' => 'required|numeric|min:0',

                'recommandeur_credit' => 'nullable|string',

                'lignes' => 'required|array|min:1',
                'lignes.*.article_id' => 'required|exists:articles,id',
                'lignes.*.depot_id' => 'required|exists:depots,id',
                'lignes.*.quantite' => 'required|numeric|min:0.01',
                'lignes.*.tarification_id' => 'required',

                'type_facture' => 'required|in:simple,normaliser',
                'observations' => 'nullable|string',
                'moyen_reglement' => 'required|in:espece,cheque,virement,carte_bancaire,MoMo,Flooz,Celtis_Pay,Effet,Avoir',
            ], [
                'date_facture.required' => 'La date de facture est obligatoire.',
                'date_facture.date' => 'La date de facture doit être une date valide.',

                'client_id.required' => 'Le client est obligatoire.',
                'client_id.exists' => "Le client sélectionné n'existe pas.",

                'date_echeance.date' => "La date d'échéance doit être une date valide.",

                'montant_regle.required' => 'Le montant réglé est obligatoire.',
                'montant_regle.numeric' => 'Le montant réglé doit être un nombre.',
                'montant_regle.min' => 'Le montant réglé ne peut pas être négatif.',

                'recommandeur_credit.string' => 'Le recommandeur crédit doit être une chaîne de caractères.',

                'lignes.required' => 'Au moins une ligne de facture est requise.',
                'lignes.array' => 'Les lignes doivent être un tableau.',
                'lignes.min' => 'Au moins une ligne de facture est requise.',

                'lignes.*.article_id.required' => 'L\'article est obligatoire pour chaque ligne.',
                'lignes.*.article_id.exists' => "L'article sélectionné n'existe pas.",

                'lignes.*.depot_id.required' => 'Le dépôt est obligatoire pour chaque ligne.',
                'lignes.*.depot_id.exists' => "Le dépôt sélectionné n'existe pas.",

                'lignes.*.quantite.required' => 'La quantité est obligatoire pour chaque ligne.',
                'lignes.*.quantite.numeric' => 'La quantité doit être un nombre.',
                'lignes.*.quantite.min' => 'La quantité doit être supérieure à 0.',

                'lignes.*.tarification_id.required' => 'La tarification est obligatoire pour chaque ligne.',

                'type_facture.required' => 'Le type de facture est obligatoire.',
                'type_facture.in' => 'Le type de facture doit être "simple" ou "normaliser".',

                'observations.string' => 'Les observations doivent être une chaîne de caractères.',

                'moyen_reglement.required' => 'Le moyen de règlement est obligatoire.',
                'moyen_reglement.in' => 'Le moyen de règlement sélectionné est invalide.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Credit file handling
            $preuve_credit = null;
            if ($request->hasFile("preuve_credit")) {
                $fileName = time() . '_' . $request->file('preuve_credit')->getClientOriginalName();

                $request->file('preuve_credit')->move("preuve_credit", $fileName);

                $preuve_credit = asset("/preuve_credit/" . $fileName);
            }

            /** 
             * Verification de l'unicité des articles dans les données envoyées
             * && vérification des quantités disponibles dans la base de données
             */
            $selectedArticles = [];
            foreach ($request->lignes as $ligne) {
                $depot = Depot::find($ligne["depot_id"]);
                $article = Article::find($ligne['article_id']);

                /**
                 * on verifie si les articles selectionnés
                 * sont tous dans son depôts pour les non admins
                 */
                if (!auth()->user()->hasRole('Super Administrateur')) {
                    $userPv = auth()->user()->pointDeVente;
                    $userPv_depotIds = $userPv->depot->pluck("id")->toArray(); //les depots du users

                    if (!in_array($ligne["depot_id"], $userPv_depotIds)) {
                        return response()->json([
                            'status' => false,
                            'message' => "Le dépôt ($depot->libelle_depot) ne vous appartient pas! Vous ne pouvez pas y passer une ecriture "
                        ], 500);
                    }
                }

                /**
                 * on verifie si l'article a été choisi deux fois
                 */
                if (isset($selectedArticles[$ligne['article_id']])) {
                    return response()->json([
                        'status' => false,
                        'message' => "L'article ({$article->code_article}) a été sélectionné plusieurs fois. Veuillez regrouper les quantités dans une seule ligne."
                    ], 422);
                }
                $selectedArticles[$ligne['article_id']] = true; //on conpte cet article comme déjà selectionné


                //On verifie si les quantités saisies au niveau des articles ne depasse pas le reste de quantité sur l'article
                $stock = StockDepot::where('depot_id', $ligne["depot_id"])
                    ->where('article_id', $ligne['article_id'])
                    ->first();

                /**
                 * Recherche de la conversion
                 */
                $venteUnite = UniteMesure::findOrFail($ligne['unite_vente_id']);
                $article = Article::findOrFail($ligne['article_id']);

                $conversion = $this->serviceStockEntree
                    ->rechercherConversion(
                        $ligne['unite_vente_id'],
                        $article->unite_mesure_id, // $stock->unite_mesure_id,
                        $stock->article_id
                    );

                if (!$conversion) {
                    return response()->json([
                        'status' => false,
                        'message' => "Il n'y a pas de conversion de l'unité ($venteUnite->libelle_unite) vers ({{$article->uniteMesure?->libelle_unite}}) pour l'article ($article->code_article), ni l'inverse! Veuillez créer cette conversion afin de continuer l'opération"
                    ], 500);
                }

                /**Qte de Base */
                $qantiteBase = $stock->quantite_reelle;

                /**Qte de requete */
                $stock->qantiteRequete = $stock->quantite_requete;

                /**Qte Vendue */
                /**
                 * on precise true pour préciser que c'est toutes les ventes(validée ou pas) qui sont considerées
                 * */
                $qteTotalVendu = $stock->article->qteVendu($stock->depot_id);

                /**Qte Reste */
                $resteStock = ($qantiteBase + $stock->qantiteRequete) - $qteTotalVendu; //$article->reste($stock->depot_id);

                Log::debug("La conversion :", ["conversion" => $conversion]);
                Log::debug("La quantite_reelle :", ["quantite_reelle" => $stock->quantite_reelle]);
                Log::debug("La qantiteBase :", ["qantiteBase" => $qantiteBase]);
                Log::debug("La qteTotalVendu :", ["qteTotalVendu" => $qteTotalVendu]);
                Log::debug("La quantite reste :", ["qteReste" => $resteStock]);

                // unite de vente vers unite de base de l'article
                $QteConvertie = $this->serviceStockEntree
                    ->convertirQuantite($ligne['quantite'], $conversion, $ligne['unite_vente_id']);

                Log::debug("Quantité convertie", [
                    "quantite_saisie" => $ligne['quantite'],
                    "quantite_convertie" => $QteConvertie,
                    "unite_vente" => $venteUnite->libelle_unite,
                    "unite_article" => $article->uniteMesure?->libelle_unite,
                ]);

                // on verifie la quantité restante de l'article dans le depot est suffisante
                if ($resteStock < $QteConvertie) {
                    return response()->json([
                        'status' => false,
                        'message' => "Le reste du stock de l'article ($article->code_article) est de $resteStock {{$article->uniteMesure?->libelle_unite}} dans le depôt ({$stock->depot?->libelle_depot})! Vous avez saisi $QteConvertie {{$article->uniteMesure?->libelle_unite}}!  Stock insuiffisant par rapport à la quantité saisie"
                    ], 500);
                }
            }

            DB::beginTransaction();

            try {
                // Mise à jour de la facture
                $facture->update([
                    'date_facture' => Carbon::parse($request->date_facture)->startOfDay(),
                    'client_id' => $request->client_id,
                    'date_echeance' => Carbon::parse($request->date_echeance)->startOfDay(),
                    'session_caisse_id' => $sessionCaisse->id,
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
                    'montant_regle' => $request->montant_regle,

                    // credit
                    "preuve_credit" => $preuve_credit,
                    "recommandeur_credit" => $request->recommandeur_credit,
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
                        'session_caisse_id' => $sessionCaisse->id,
                        'created_by' => auth()->id(),
                    ]);
                    $facture->reglements()->save($reglement);
                }

                // verification du depassement du plafond de crédit
                $montant = -$facture->client->solde() + $facture->montant_ttc;
                $plafondCredit = $facture->client?->plafond_credit;

                if ($plafondCredit > 0 && $montant > $plafondCredit) {
                    throw new Exception("Le montant $ {$facture->montant_ttc} de la vente, ajouté au solde $ {$facture->client->solde()} dépasse son plafond de crédit $ {$plafondCredit}");
                };


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

                    $ligneFacture = new LigneFacture([
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

                // Mise à jour du compte client
                if ($facture->compteClient->isNotEmpty()) {
                    $facture->compteClient
                        ->first()
                        ->update([
                            'date_op' => $facture->date_facture,
                            'montant_op' => $facture->montant_ttc - $facture->montant_remise,
                            'client_id' => $facture->client_id,
                            'user_id' => Auth::user()->id,
                            'type_op' => 'FAC_CLT',
                        ]);
                }

                $sessionCaisse->mettreAJourTotaux();
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Facture mise à jour avec succès',
                    'data' => ['facture' => $facture->load([
                        'client',
                        'lignes.article',
                        'lignes.uniteVente',
                        'sessionCaisse',
                        'reglements'
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

    public function updateReference(Request $request, FactureClient $facture)
    {
        Log::debug("Début de modification de la reference :", ["data" => $request->all()]);

        try {
            DB::beginTransaction();

            $isReferenceExiste = FactureClient::query()
                ->where("id", "!=", $facture->id)
                ->where("reference_recu", $request->reference_recu)
                ->first();

            if ($isReferenceExiste) {
                throw new Exception("Cette reference existe déjà");
            }

            // Mise à jour de la facture
            $facture->update([
                'reference_recu' => $request->reference_recu,
            ]);

            DB::commit();

            return redirect()
                ->back()
                ->with("message", "Reference mise à jour avec succès");
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with("error", $e->getMessage());
        }
    }

    public function searchArticles(Request $request)
    {
        $search = $request->get('q');
        Log::info("Terme de recherche :", ["terme" => $search]);
        $user = auth()->user();

        $stocks = StockDepot::with('article.uniteMesure', 'depot')
            ->get()
            ->filter(function ($stock) use ($search, $user) {
                /** POUR UN ADMIN OU UN CHARGE DES STOCKS, ON NE FAIT PAS DE FILTRE */
                if ($user->hasRole('Super Administrateur') || $user->hasRole('CHARGE DES STOCKS ET SUIVI DES ACHATS')) {
                    return (
                        str_contains(strtolower($stock->article->designation), strtolower($search)) ||
                        str_contains(strtolower($stock->article->code_article), strtolower($search))
                    );
                }

                /** ON FILTRE LES STOCKS SELON LES POINT DE VENTE DU USER */
                $userPv = auth()->user()->pointDeVente;
                $userPv_depotIds = $userPv->depot->pluck("id")->toArray(); //les depots du users

                if (in_array($stock->depot_id, $userPv_depotIds)) {
                    return (str_contains(strtolower($stock->article->designation), strtolower($search)) ||
                        str_contains(strtolower($stock->article->code_article), strtolower($search))
                    );
                }
            });

        return response()->json([
            'results' => $stocks->map(function ($stock) {
                /**
                 * @param $resteStock Reste du stock dans le depot
                 */

                $conversion = $this->serviceStockEntree
                    ->rechercherConversion(
                        $stock->unite_mesure_id,
                        $stock->article->unite_mesure_id,
                        $stock->article_id
                    );

                /**Qte de Base */
                $qantiteBase = $conversion ? $this->serviceStockEntree
                    ->convertirQuantite(
                        $stock->quantite_reelle,
                        $conversion,
                        $stock->unite_mesure_id
                    ) : 00;

                /**Qte de requete */
                $stock->qantiteRequete = $stock->quantite_requete;

                /**Qte Vendue */
                /**
                 * on precise true pour préciser que c'est toutes les ventes(validée ou pas) qui sont considerées
                 * */
                $qteTotalVendu = $stock->article->qteVendu($stock->depot_id, true);

                /**Qte Reste */
                $resteStock = ($qantiteBase + $stock->qantiteRequete) - $qteTotalVendu; //$article->reste($stock->depot_id);

                Log::debug("La quantite requete :", ["qte" => $stock->qantiteRequete]);
                Log::debug("La quantite reste :", ["qteReste" => $resteStock]);
                // $resteStock = $qantiteBase - $qteTotalVendu; //$article->reste($stock->depot_id);

                return [
                    'id' => $stock->article->id,
                    'text' => $stock->article->designation,
                    'code_article' => $stock->article->code_article,
                    'depot' => $stock->depot,
                    'unite_mesure' => $stock->article->uniteMesure, // $stock->uniteMesure, //->libelle_unite,
                    'stock' => $resteStock ?? 00,
                ];
            })
        ]);
    }

    public function getTarifs(Request $request, $articleId)
    {
        try {
            $article = Article::with(['tarifications.typeTarif'])
                ->findOrFail($articleId);

            $tarifs = $article->tarifications
                ->where('statut', true)
                ->map(function ($tarif) {
                    return [
                        'id' => $tarif->id,
                        'text' => sprintf(
                            '%s FCFA - %s',
                            number_format($tarif->prix, 2),
                            $tarif->typeTarif->libelle_type_tarif ?? ''
                        ),
                        'prix' => $tarif->prix
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'tarifs' => $tarifs
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des tarifs'
            ], 500);
        }
    }

    public function getUnites($articleId)
    {
        try {
            Log::info('Début récupération des unités', ['article_id' => $articleId]);

            // Récupérer l'article avec son unité de mesure
            $article = Article::with('uniteMesure', 'tarifications')->findOrFail($articleId);

            $unites = collect();


            // 1. Ajouter l'unité de base de l'article si elle existe
            if ($article->uniteMesure) {
                // $tarification =  $article->tarifViaUnite($article->uniteMesure->id);
                $unites->push([
                    'id' => $article->uniteMesure->id,
                    'text' => $article->uniteMesure?->libelle_unite,
                    // 'text' => $article->uniteMesure?->libelle_unite . ' (' . $tarification?->typeTarif?->libelle_type_tarif . ' | ' . $tarification?->depotTarif?->libelle_depot . ' | ' . $tarification?->prix . ' ' . "FCFA )",
                ]);
            }

            // 2. Obtenir toutes les unités ayant des conversions pour cet article
            $unitesConversion = ConversionUnite::where('article_id', $articleId)
                ->where('statut', true)
                ->with(['uniteSource', 'uniteDest'])
                ->get();

            // Ajouter les unités source actives
            $unitesConversion->pluck('uniteSource')
                ->where('statut', true)
                ->unique('id')
                ->each(function ($unite) use (&$unites, $article) {
                    if (!$unites->contains('id', $unite->id)) {
                        // $tarification =  $article->tarifViaUnite($unite->id);
                        $unites->push([
                            'id' => $unite->id,
                            'text' => $unite->libelle_unite,
                            // 'text' =>  $unite?->libelle_unite . ' (' . $tarification?->typeTarif?->libelle_type_tarif . ' | ' . $tarification?->depotTarif?->libelle_depot . ' | ' . $tarification?->prix . ' ' . "FCFA )",
                        ]);
                    }
                });

            // Ajouter les unités destination actives
            $unitesConversion->pluck('uniteDest')
                ->where('statut', true)
                ->unique('id')
                ->each(function ($unite) use (&$unites, $article) {
                    if (!$unites->contains('id', $unite->id)) {
                        // $tarification =  $article->tarifViaUnite($unite->id);
                        $unites->push([
                            'id' => $unite->id,
                            'text' => $unite->libelle_unite,
                            // 'text' =>  $unite?->libelle_unite . ' (' . $tarification?->typeTarif?->libelle_type_tarif . ' | ' . $tarification?->depotTarif?->libelle_depot . ' | ' . $tarification?->prix . ' ' . "FCFA )",
                        ]);
                    }
                });

            Log::info('Unités récupérées avec succès', [
                'article_id' => $articleId,
                'nombre_unites' => $unites->count(),
                'unites' => $unites->toArray()
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'unites' => $unites->values()->all()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des unités', [
                'article_id' => $articleId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des unités: ' . $e->getMessage()
            ], 500);
        }
    }

    // get depot's factures
    public static function getDepotFactures($depotId)
    {
        Log::debug("Depot selectionné :", ["depot" => Depot::find($depotId)]);

        $factures = FactureClient::whereHas('lignes', function ($ligne) use ($depotId) {
            $ligne->where('depot', $depotId)
                /**on recupere seulement les lignes qui disposent encore de quantité */
                ->where(function ($q) {
                    $q->whereNull('quantite_livree_simple')
                        ->orWhere(function ($subQ) {
                            $subQ->whereNotNull('quantite_livree_simple')
                                ->where('quantite', '>', DB::raw('quantite_livree_simple'));
                        });
                });
        })->get(["id", "numero"]);

        Log::debug("Les factures recupérées :", ["data" => $factures]);

        return response()->json([
            'data' => $factures
        ]);
    }

    public function show($id)
    {
        try {
            Log::info('Début du chargement des détails de la facture', ['facture_id' => $id]);

            $facture = FactureClient::with([
                'client',
                'lignes.article',
                'lignes.facturedepot',
                'lignes.uniteVente',
                'lignes.tarification.typeTarif',
                'sessionCaisse',
                'createdBy'
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
                'message' => 'Une erreur est survenue lors du chargement des détails de la facture'
            ], 500);
        }
    }

    public function validateFacture($id)
    {
        Log::debug("Debut de validation de la facture", ["data" => $id]);
        try {
            DB::beginTransaction();

            $facture = FactureClient::with(['client', 'lignes.article', 'reglements'])
                ->findOrFail($id);

            if ($facture->statut === 'validee') {
                throw new Exception('Facture déjà validée');
            }

            $sessionCaisse = SessionCaisse::ouverte()
                ->where('point_de_vente_id', auth()->user()->point_de_vente_id)
                ->first() ?? SessionCaisse::latest()->first();

            if (!$sessionCaisse) {
                throw new Exception('Session de caisse requise');
            }

            $updateData = [
                'date_validation' => now(),
                'validated_by' => auth()->id(),
                'statut' => 'validee'
            ];

            $facture->update($updateData);

            if ($reglement = $facture->reglements->first()) {
                $reglement->update([
                    'date_validation' => now(),
                    'validated_by' => auth()->id(),
                    'statut' => 'validee'
                ]);

                /**
                 *  Creation du compte client si c'est pas encore fait
                 *  */
                if ($reglement->compteClient->isEmpty()) {
                    $reglement->compteClient()->create([
                        'date_op' => $reglement->date_reglement,
                        'montant_op' => $reglement->montant,
                        'client_id' => $reglement->facture?->client_id,
                        'user_id' => Auth::user()->id,
                        'type_op' => 'REG_CLT',
                    ]);
                } else {
                    $reglement->compteClient()
                        ->first()
                        ->update([
                            'date_op' => $reglement->date_reglement,
                            'montant_op' => $reglement->montant,
                            'client_id' => $reglement->facture?->client_id,
                            'user_id' => Auth::user()->id,
                            'type_op' => 'REG_CLT',
                        ]);
                }
            }

            $sessionCaisse->mettreAJourTotaux();

            Log::info("La facture concernée ", ["facture" => $facture]);

            if ($facture->compteClient->isEmpty()) {
                $facture->compteClient()->create([
                    'date_op' => $facture->date_facture,
                    'montant_op' => $facture->montant_ttc - $facture->montant_remise,
                    'client_id' => $facture->client_id,
                    'user_id' => Auth::user()->id,
                    'type_op' => 'FAC_CLT',
                ]);
            } else {
                $facture->compteClient
                    ->first()
                    ->update([
                        'date_op' => $facture->date_facture,
                        'montant_op' => $facture->montant_ttc - $facture->montant_remise,
                        'client_id' => $facture->client_id,
                        'user_id' => Auth::user()->id,
                        'type_op' => 'FAC_CLT',
                    ]);
            }

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

            $facture = FactureClient::findOrFail($id);

            // Vérifier le statut
            if ($facture->statut === 'validee') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Impossible de supprimer une facture validée'
                ], 422);
            }

            // suppression du compte client lié à la facture
            $facture->compteClient()->delete();

            // Supprimer les règlements de manière forcée
            $facture->reglements()->forceDelete();

            // Supprimer les lignes
            $facture->lignes()->delete();

            // Supprimer la facture
            $facture->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Facture et règlements supprimés avec succès'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression facture', [
                'facture_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    public function details(FactureClient $facture)
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

    public function print(Request $request, FactureClient $facture)
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
        $montantTTc = $request->get("montantTTc");

        $pdf = PDF::loadView('pages.ventes.facture.partials.print-facture', compact('facture', "logo", "montantTTc"));
        $pdf->setPaper('a4');

        return $pdf->stream("facture_{$facture->numero}.pdf");
    }

    /**
     * Bon à livrer
     */
    public function bonALivrer(Request $request, FactureClient $facture)
    {
        // Chargement des relations nécessaires
        $facture->load([
            'client',
            'lignes.article',
            'lignes.uniteVente',
            'lignes.facturedepot',
            'createdBy',
            'validatedBy'
        ]);

        $entete = $request->get("entete");

        $pdf = PDF::loadView('pages.ventes.facture.partials.bon-a-livrer', compact('facture', 'entete'));
        $pdf->setPaper('a4');

        return $pdf->stream("bon_a_livrer_{$facture->numero}.pdf");
    }

    /**
     * Obtenir les détails d'une facture
     *
     * @param FactureClient $facture
     * @return JsonResponse
     */

    public function getDetailsFacture(FactureClient $facture)
    {
        Log::debug("Récupération des détails de la facture", ["facture_id" => $facture->id]);
        // Changement dans le chargement des relations
        $facture->load([
            'client',
            'sessionCaisse', // On charge d'abord la session
            'lignes.article'
        ]);

        return response()->json(
            [
                'numero' => $facture->numero,
                'date_facture' => $facture->date_facture->format('d/m/Y'),
                'client' => [
                    'raison_sociale' => $facture->client->raison_sociale
                ],
                'point_vente' => $facture->sessionCaisse ? [
                    'libelle' => $facture->sessionCaisse->point_de_vente_id ?
                        PointDeVente::find($facture->sessionCaisse->point_de_vente_id)->nom_pv : '-'
                ] : null,
                'montant_ht' => number_format($facture->montant_ht, 0, ',', ' '),
                'montant_tva' => number_format($facture->montant_tva, 0, ',', ' '),
                'montant_ttc' => number_format($facture->montant_ttc, 0, ',', ' '),
                'lignes' => $facture->lignes->map(function ($ligne) {
                    return [
                        'article' => [
                            'designation' => $ligne->article->designation
                        ],
                        'quantite' => number_format($ligne->quantite, 0, ',', ' '),
                        'prix_unitaire' => number_format($ligne->prix_unitaire_ht, 0, ',', ' '),
                        'montant_total' => number_format($ligne->montant_ttc, 0, ',', ' ')
                    ];
                })
            ]
        );
    }
}
