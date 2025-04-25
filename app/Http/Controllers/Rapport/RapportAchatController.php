<?php

namespace App\Http\Controllers\Rapport;

use App\Http\Controllers\Controller;
use App\Models\Achat\{ProgrammationAchat, BonCommande, FactureFournisseur, BonLivraisonFournisseur, ReglementFournisseur};
use App\Models\Achat\Fournisseur;
use App\Models\Parametre\{PointDeVente, Depot};
use Illuminate\Http\Request;
use Illuminate\Http\DB;

class RapportAchatController extends Controller
{
    public function rapportProgrammations(Request $request)
    {
        $query = ProgrammationAchat::with([
            'fournisseur',
            'pointVente',
            'lignes.article',
            'lignes.uniteMesure',
            'validator',
            'creator',
            'updater'
        ]);

        if ($request->date_debut) {
            $query->whereDate('date_programmation', '>=', $request->date_debut);
        }
        if ($request->date_fin) {
            $query->whereDate('date_programmation', '<=', $request->date_fin);
        }
        if ($request->fournisseur_id) {
            $query->where('fournisseur_id', $request->fournisseur_id);
        }
        if ($request->point_de_vente_id) {
            $query->where('point_de_vente_id', $request->point_de_vente_id);
        }
        if ($request->statut_validation && $request->statut_validation !== 'tous') {
            $query->when(
                $request->statut_validation === 'valide',
                fn ($q) => $q->whereNotNull('validated_at'),
                fn ($q) => $q->whereNull('validated_at')
            );
        }

        $statsQuery = clone $query;

        $programmations = $query->orderBy('date_programmation', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('pages.rapports.achats.pre-commande', [
            'programmations' => $programmations,
            'statistiques' => [
                'total_programmations' => $programmations->total(),
                'programmations_validees' => $statsQuery->whereNotNull('validated_at')->count(),
                'programmations_non_validees' => $statsQuery->whereNull('validated_at')->count(),
            ],
            'filtres' => [
                'fournisseurs' => Fournisseur::select('id', 'raison_sociale as nom')->orderBy('raison_sociale')->get(),
                'points_vente' => PointDeVente::select('id', 'nom_pv as libelle')->orderBy('nom_pv')->get()
            ],
            'params' => [
                'date_debut' => $request->date_debut ?? now()->format('Y-m-d'),
                'date_fin' => $request->date_fin ?? now()->format('Y-m-d'),
                'fournisseur_id' => $request->fournisseur_id,
                'point_de_vente_id' => $request->point_de_vente_id,
                'statut_validation' => $request->statut_validation ?? 'tous',
            ]
        ]);
    }

    public function exportProgrammations(Request $request)
    {
        $query = ProgrammationAchat::with([
            'fournisseur',
            'pointVente',
            'lignes.article',
            'lignes.uniteMesure',
            'validator'
        ]);

        if ($request->date_debut) {
            $query->whereDate('date_programmation', '>=', $request->date_debut);
        }
        if ($request->date_fin) {
            $query->whereDate('date_programmation', '<=', $request->date_fin);
        }
        if ($request->fournisseur_id) {
            $query->where('fournisseur_id', $request->fournisseur_id);
        }
        if ($request->point_de_vente_id) {
            $query->where('point_de_vente_id', $request->point_de_vente_id);
        }
        if ($request->statut_validation && $request->statut_validation !== 'tous') {
            $query->when(
                $request->statut_validation === 'valide',
                fn ($q) => $q->whereNotNull('validated_at'),
                fn ($q) => $q->whereNull('validated_at')
            );
        }

        $programmations = $query->orderBy('date_programmation', 'desc')->get();

        // TODO: Implémenter l'export Excel
        // return Excel::download(new ProgrammationsExport($programmations), 'programmations.xlsx');
    }

    public function rapportBonCommandes(Request $request)
    {
        $query = BonCommande::with([
            'fournisseur',
            'pointVente',
            'programmation',
            'lignes.article',
            'lignes.uniteMesure',
            'validator',
            'creator',
            'updater',
            'factures'
        ]);

        if ($request->date_debut) {
            $query->whereDate('date_commande', '>=', $request->date_debut);
        }
        if ($request->date_fin) {
            $query->whereDate('date_commande', '<=', $request->date_fin);
        }
        if ($request->fournisseur_id) {
            $query->where('fournisseur_id', $request->fournisseur_id);
        }
        if ($request->point_de_vente_id) {
            $query->where('point_de_vente_id', $request->point_de_vente_id);
        }
        if ($request->statut_validation && $request->statut_validation !== 'tous') {
            $query->when(
                $request->statut_validation === 'valide',
                fn ($q) => $q->whereNotNull('validated_at'),
                fn ($q) => $q->whereNull('validated_at')
            );
        }

        $statsQuery = clone $query;

        $bonCommandes = $query->orderBy('date_commande', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('pages.rapports.achats.bon-commande', [
            'bonCommandes' => $bonCommandes,
            'statistiques' => [
                'total_commandes' => $bonCommandes->total(),
                'commandes_validees' => $statsQuery->whereNotNull('validated_at')->count(),
                'commandes_non_validees' => $statsQuery->whereNull('validated_at')->count(),
                'montant_total' => $statsQuery->sum('montant_total'),
                'montant_valide' => $statsQuery->whereNotNull('validated_at')->sum('montant_total')
            ],
            'filtres' => [
                'fournisseurs' => Fournisseur::select('id', 'raison_sociale as nom')->orderBy('raison_sociale')->get(),
                'points_vente' => PointDeVente::select('id', 'nom_pv as libelle')->orderBy('nom_pv')->get()
            ],
            'params' => [
                'date_debut' => $request->date_debut ?? now()->format('Y-m-d'),
                'date_fin' => $request->date_fin ?? now()->format('Y-m-d'),
                'fournisseur_id' => $request->fournisseur_id,
                'point_de_vente_id' => $request->point_de_vente_id,
                'statut_validation' => $request->statut_validation ?? 'tous',
            ]
        ]);
    }

    public function rapportFactures(Request $request)
    {
        $query = FactureFournisseur::with([
            'fournisseur',
            'pointVente',
            'bonCommande',
            'lignes.article',
            'lignes.uniteMesure',
            'validator',
            'creator',
            'updater',
            'reglements'
        ]);

        if ($request->date_debut) {
            $query->whereDate('date_facture', '>=', $request->date_debut);
        }
        if ($request->date_fin) {
            $query->whereDate('date_facture', '<=', $request->date_fin);
        }
        if ($request->fournisseur_id) {
            $query->where('fournisseur_id', $request->fournisseur_id);
        }
        if ($request->point_de_vente_id) {
            $query->where('point_de_vente_id', $request->point_de_vente_id);
        }
        if ($request->statut_paiement && $request->statut_paiement !== 'tous') {
            $query->where('statut_paiement', $request->statut_paiement);
        }
        if ($request->statut_validation && $request->statut_validation !== 'tous') {
            if ($request->statut_validation === 'valide') {
                $query->whereNotNull('validated_at');
            } else {
                $query->whereNull('validated_at');
            }
        }

        $statsQuery = clone $query;

        $factures = $query->orderBy('date_facture', 'desc')
            ->paginate(15)
            ->withQueryString();

        $statistiques = [
            'total_factures' => $factures->total(),
            'factures_validees' => $statsQuery->whereNotNull('validated_at')->count(),
            'factures_non_validees' => $statsQuery->whereNull('validated_at')->count(),
            'montant_total' => $statsQuery->sum('montant_ttc'),
            'montant_valide' => $statsQuery->whereNotNull('validated_at')->sum('montant_ttc'),
            'montant_non_paye' => $statsQuery->where('statut_paiement', 'NON_PAYE')->sum('montant_ttc'),
            'montant_partiel' => $statsQuery->where('statut_paiement', 'PARTIELLEMENT_PAYE')->sum('montant_ttc'),
            'montant_paye' => $statsQuery->where('statut_paiement', 'PAYE')->sum('montant_ttc')
        ];

        return view('pages.rapports.achats.facture-achat', compact(
            'factures',
            'statistiques'
        ))->with([
            'filtres' => [
                'fournisseurs' => Fournisseur::select('id', 'raison_sociale as nom')->orderBy('raison_sociale')->get(),
                'points_vente' => PointDeVente::select('id', 'nom_pv as libelle')->orderBy('nom_pv')->get(),
                'statuts_paiement' => [
                    'NON_PAYE' => 'Non payé',
                    'PARTIELLEMENT_PAYE' => 'Partiellement payé',
                    'PAYE' => 'Payé'
                ]
            ],
            'params' => [
                'date_debut' => $request->date_debut ?? now()->format('Y-m-d'),
                'date_fin' => $request->date_fin ?? now()->format('Y-m-d'),
                'fournisseur_id' => $request->fournisseur_id,
                'point_de_vente_id' => $request->point_de_vente_id,
                'statut_validation' => $request->statut_validation ?? 'tous',
                'statut_paiement' => $request->statut_paiement ?? 'tous'
            ]
        ]);
    }

    public function rapportLivraisons(Request $request)
    {
        $query = BonLivraisonFournisseur::with([
            'fournisseur',
            'pointDeVente',
            'depot',
            'facture',
            'lignes.article',
            'lignes.uniteMesure',
            'lignes.uniteSupplementaire',
            'vehicule',
            'chauffeur',
            'validator',
            'creator',
            'updater'
        ]);

        if ($request->date_debut) {
            $query->whereDate('date_livraison', '>=', $request->date_debut);
        }
        if ($request->date_fin) {
            $query->whereDate('date_livraison', '<=', $request->date_fin);
        }
        if ($request->fournisseur_id) {
            $query->where('fournisseur_id', $request->fournisseur_id);
        }
        if ($request->point_de_vente_id) {
            $query->where('point_de_vente_id', $request->point_de_vente_id);
        }
        if ($request->depot_id) {
            $query->where('depot_id', $request->depot_id);
        }
        if ($request->statut_validation && $request->statut_validation !== 'tous') {
            if ($request->statut_validation === 'valide') {
                $query->whereNotNull('validated_at');
            } else {
                $query->whereNull('validated_at');
            }
        }

        $statsQuery = clone $query;

        $livraisons = $query->orderBy('date_livraison', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Calcul des totaux par dépôt
        $totauxParDepot = $statsQuery->whereNotNull('validated_at')
            ->select('depot_id')
            ->selectRaw('COUNT(*) as total_livraisons')
            ->groupBy('depot_id')
            ->with('depot')
            ->get()
            ->keyBy('depot_id');

        return view('pages.rapports.achats.livraison-achat', [
            'livraisons' => $livraisons,
            'statistiques' => [
                'total_livraisons' => $livraisons->total(),
                'livraisons_validees' => $statsQuery->whereNotNull('validated_at')->count(),
                'livraisons_non_validees' => $statsQuery->whereNull('validated_at')->count(),
                'totaux_par_depot' => $totauxParDepot
            ],
            'filtres' => [
                'fournisseurs' => Fournisseur::select('id', 'raison_sociale as nom')->orderBy('raison_sociale')->get(),
                'points_vente' => PointDeVente::select('id', 'nom_pv as libelle')->orderBy('nom_pv')->get(),
                'depots' => Depot::select('id', 'libelle_depot as nom')->orderBy('libelle_depot')->get()
            ],
            'params' => [
                'date_debut' => $request->date_debut ?? now()->format('Y-m-d'),
                'date_fin' => $request->date_fin ?? now()->format('Y-m-d'),
                'fournisseur_id' => $request->fournisseur_id,
                'point_de_vente_id' => $request->point_de_vente_id,
                'depot_id' => $request->depot_id,
                'statut_validation' => $request->statut_validation ?? 'tous'
            ]
        ]);
    }

    public function rapportReglements(Request $request)
    {
        $query = ReglementFournisseur::with([
            'facture.fournisseur',
            'facture.pointVente',
            'validator',
            'creator',
            'updater'
        ]);

        if ($request->date_debut) {
            $query->whereDate('date_reglement', '>=', $request->date_debut);
        }
        if ($request->date_fin) {
            $query->whereDate('date_reglement', '<=', $request->date_fin);
        }
        if ($request->fournisseur_id) {
            $query->whereHas('facture', function ($q) use ($request) {
                $q->where('fournisseur_id', $request->fournisseur_id);
            });
        }
        if ($request->point_de_vente_id) {
            $query->whereHas('facture', function ($q) use ($request) {
                $q->where('point_de_vente_id', $request->point_de_vente_id);
            });
        }
        if ($request->mode_reglement && $request->mode_reglement !== 'tous') {
            $query->where('mode_reglement', $request->mode_reglement);
        }
        if ($request->statut_validation && $request->statut_validation !== 'tous') {
            $query->when(
                $request->statut_validation === 'valide',
                fn ($q) => $q->whereNotNull('validated_at'),
                fn ($q) => $q->whereNull('validated_at')
            );
        }

        $statsQuery = clone $query;

        $reglements = $query->orderBy('date_reglement', 'desc')
            ->paginate(15)
            ->withQueryString();

        $statistiques = [
            'total_reglements' => $reglements->total(),
            'reglements_valides' => $statsQuery->whereNotNull('validated_at')->count(),
            'reglements_non_valides' => $statsQuery->whereNull('validated_at')->count(),
            'montant_total' => $statsQuery->sum('montant_reglement'),
            'montant_valide' => $statsQuery->whereNotNull('validated_at')->sum('montant_reglement'),
            'par_mode' => [
                'ESPECE' => $statsQuery->where('mode_reglement', ReglementFournisseur::MODE_ESPECE)->sum('montant_reglement'),
                'CHEQUE' => $statsQuery->where('mode_reglement', ReglementFournisseur::MODE_CHEQUE)->sum('montant_reglement'),
                'VIREMENT' => $statsQuery->where('mode_reglement', ReglementFournisseur::MODE_VIREMENT)->sum('montant_reglement'),
                'DECHARGE' => $statsQuery->where('mode_reglement', ReglementFournisseur::MODE_DECHARGE)->sum('montant_reglement'),
                'AUTRES' => $statsQuery->where('mode_reglement', ReglementFournisseur::MODE_AUTRES)->sum('montant_reglement')
            ]
        ];

        return view('pages.rapports.achats.reglement-achat', [
            'reglements' => $reglements,
            'statistiques' => $statistiques,
            'filtres' => [
                'fournisseurs' => Fournisseur::select('id', 'raison_sociale as nom')
                    ->orderBy('raison_sociale')
                    ->get(),
                'points_vente' => PointDeVente::select('id', 'nom_pv as libelle')
                    ->orderBy('nom_pv')
                    ->get(),
                'modes_reglement' => [
                    'ESPECE' => 'Espèces',
                    'CHEQUE' => 'Chèque',
                    'VIREMENT' => 'Virement',
                    'DECHARGE' => 'Décharge',
                    'AUTRES' => 'Autres'
                ]
            ],
            'params' => [
                'date_debut' => $request->date_debut ?? now()->format('Y-m-d'),
                'date_fin' => $request->date_fin ?? now()->format('Y-m-d'),
                'fournisseur_id' => $request->fournisseur_id,
                'point_de_vente_id' => $request->point_de_vente_id,
                'mode_reglement' => $request->mode_reglement ?? 'tous',
                'statut_validation' => $request->statut_validation ?? 'tous'
            ]
        ]);
    }

    // public function rapportCompteFournisseur(Request $request)
    // {
    //     $query = Fournisseur::query()
    //         ->withSum(['factures as total_factures' => function($q) {
    //             $q->whereNotNull('facture_fournisseurs.validated_at')
    //               ->whereNull('facture_fournisseurs.deleted_at');
    //         }], 'montant_ttc')
    //         ->withSum(['factures as total_reglements' => function($q) {
    //             $q->whereNotNull('facture_fournisseurs.validated_at')
    //               ->whereNull('facture_fournisseurs.deleted_at')
    //               ->whereHas('reglements', function($q) {
    //                   $q->whereNotNull('reglement_fournisseurs.validated_at')
    //                     ->whereNull('reglement_fournisseurs.deleted_at');
    //               })
    //               ->join('reglement_fournisseurs', 'facture_fournisseurs.id', '=', 'reglement_fournisseurs.facture_fournisseur_id')
    //               ->select(\DB::raw('COALESCE(SUM(reglement_fournisseurs.montant_reglement), 0) as total_reglements'));
    //         }], 'montant_ttc')
    //         ->with(['soldeInitial' => function($q) {
    //             $q->latest('date_solde');
    //         }]);

    //     if ($request->fournisseur_id) {
    //         $query->where('id', $request->fournisseur_id);
    //     }

    //     if ($request->point_de_vente_id) {
    //         $query->whereHas('factures', function($q) use ($request) {
    //             $q->where('point_de_vente_id', $request->point_de_vente_id);
    //         });
    //     }

    //     $fournisseurs = $query->get()
    //         ->map(function ($fournisseur) {
    //             $soldeInitial = $fournisseur->soldeInitial;
    //             $montantInitial = $soldeInitial ? ($soldeInitial->type === 'CREDITEUR' ? $soldeInitial->montant : -$soldeInitial->montant) : 0;

    //             $fournisseur->solde = $montantInitial + ($fournisseur->total_factures ?? 0) - ($fournisseur->total_reglements ?? 0);
    //             return $fournisseur;
    //         });

    //     // Statistiques par mode de règlement pour un fournisseur spécifique
    //     $statsParMode = [];
    //     if ($request->fournisseur_id) {
    //         $modes = [
    //             ReglementFournisseur::MODE_ESPECE,
    //             ReglementFournisseur::MODE_CHEQUE,
    //             ReglementFournisseur::MODE_VIREMENT,
    //             ReglementFournisseur::MODE_DECHARGE,
    //             ReglementFournisseur::MODE_AUTRES
    //         ];

    //         foreach ($modes as $mode) {
    //             $statsParMode[$mode] = ReglementFournisseur::whereHas('facture', function($q) use ($request) {
    //                     $q->where('fournisseur_id', $request->fournisseur_id);
    //                 })
    //                 ->whereNotNull('validated_at')
    //                 ->where('mode_reglement', $mode)
    //                 ->sum('montant_reglement');
    //         }
    //     }

    //     // Détail des mouvements
    //     $mouvements = collect();
    //     if ($request->fournisseur_id) {
    //         $fournisseur = Fournisseur::with('soldeInitial')->find($request->fournisseur_id);
    //         $soldeInitial = $fournisseur->soldeInitial;

    //         if ($soldeInitial) {
    //             $mouvements->push([
    //                 'id' => $soldeInitial->id,
    //                 'date' => $soldeInitial->date_solde,
    //                 'type' => 'SOLDE_INITIAL',
    //                 'reference' => 'SI-' . $soldeInitial->id,
    //                 'debit' => $soldeInitial->type === 'CREDITEUR' ? $soldeInitial->montant : 0,
    //                 'credit' => $soldeInitial->type === 'DEBITEUR' ? $soldeInitial->montant : 0,
    //                 'commentaire' => $soldeInitial->commentaire
    //             ]);
    //         }

    //         $factures = FactureFournisseur::with(['pointVente', 'bonCommande'])
    //             ->where('fournisseur_id', $request->fournisseur_id)
    //             ->whereNotNull('validated_at')
    //             ->get()
    //             ->map(function ($facture) {
    //                 return [
    //                     'id' => $facture->id,
    //                     'date' => $facture->date_facture,
    //                     'type' => 'FACTURE',
    //                     'reference' => $facture->code,
    //                     'bon_commande' => $facture->bonCommande?->code,
    //                     'point_vente' => $facture->pointVente->libelle,
    //                     'debit' => $facture->montant_ttc,
    //                     'credit' => 0,
    //                     'statut_paiement' => $facture->statut_paiement
    //                 ];
    //             });

    //         $reglements = ReglementFournisseur::with('facture.pointVente')
    //             ->whereHas('facture', function ($q) use ($request) {
    //                 $q->where('fournisseur_id', $request->fournisseur_id);
    //             })
    //             ->whereNotNull('validated_at')
    //             ->get()
    //             ->map(function ($reglement) {
    //                 return [
    //                     'id' => $reglement->id,
    //                     'date' => $reglement->date_reglement,
    //                     'type' => 'REGLEMENT',
    //                     'reference' => $reglement->code,
    //                     'mode' => $reglement->mode_reglement,
    //                     'reference_paiement' => $reglement->reference_reglement,
    //                     'point_vente' => $reglement->facture->pointVente->libelle,
    //                     'debit' => 0,
    //                     'credit' => $reglement->montant_reglement
    //                 ];
    //             });

    //         $mouvements = $mouvements->concat($factures)->concat($reglements)->sortByDesc('date');
    //     }

    //     return view('pages.rapports.achats.compte-fournisseur', [
    //         'fournisseurs' => $fournisseurs,
    //         'mouvements' => $mouvements,
    //         'solde_initial' => $request->fournisseur_id ? $soldeInitial : null,
    //         'statistiques' => [
    //             'total_fournisseurs' => $fournisseurs->count(),
    //             'total_factures' => $fournisseurs->sum('total_factures'),
    //             'total_reglements' => $fournisseurs->sum('total_reglements'),
    //             'solde_global' => $fournisseurs->sum('solde'),
    //             'fournisseurs_debiteurs' => $fournisseurs->where('solde', '<', 0)->count(),
    //             'fournisseurs_crediteurs' => $fournisseurs->where('solde', '>', 0)->count(),
    //             'montant_debiteur' => $fournisseurs->where('solde', '<', 0)->sum('solde') * -1,
    //             'montant_crediteur' => $fournisseurs->where('solde', '>', 0)->sum('solde'),
    //             'par_mode' => $statsParMode
    //         ],
    //         'filtres' => [
    //             'fournisseurs' => Fournisseur::select('id', 'raison_sociale as nom')
    //                 ->orderBy('raison_sociale')
    //                 ->get(),
    //             'points_vente' => PointDeVente::select('id', 'nom_pv as libelle')
    //                 ->orderBy('nom_pv')
    //                 ->get()
    //         ],
    //         'params' => [
    //             'fournisseur_id' => $request->fournisseur_id,
    //             'point_de_vente_id' => $request->point_de_vente_id
    //         ]
    //     ]);
    // }

    public function rapportCompteFournisseur(Request $request)
{
    // Construction de la requête principale
    $query = Fournisseur::query()
        ->withSum(['factures as total_factures' => function($q) {
            $q->whereNotNull('facture_fournisseurs.validated_at')
              ->whereNull('facture_fournisseurs.deleted_at');
        }], 'montant_ttc')
        ->withSum(['factures as total_reglements' => function($q) {
            $q->whereNotNull('facture_fournisseurs.validated_at')
              ->whereNull('facture_fournisseurs.deleted_at')
              ->whereHas('reglements', function($q) {
                  $q->whereNotNull('reglement_fournisseurs.validated_at')
                    ->whereNull('reglement_fournisseurs.deleted_at');
              })
              ->join('reglement_fournisseurs', 'facture_fournisseurs.id', '=', 'reglement_fournisseurs.facture_fournisseur_id')
              ->select(\DB::raw('COALESCE(SUM(reglement_fournisseurs.montant_reglement), 0) as total_reglements'));
        }], 'montant_ttc')
        ->with(['soldeInitial' => function($q) {
            $q->latest('date_solde');
        }]);

    // Filtre par fournisseur si spécifié
    if ($request->fournisseur_id) {
        $query->where('id', $request->fournisseur_id);
    }

    // Filtre par point de vente si spécifié
    if ($request->point_de_vente_id) {
        $query->whereHas('factures', function($q) use ($request) {
            $q->where('point_de_vente_id', $request->point_de_vente_id);
        });
    }

    // Récupération et calcul des soldes
    $fournisseurs = $query->get()
        ->map(function ($fournisseur) {
            // Récupération du solde initial
            $soldeInitial = $fournisseur->soldeInitial;

            // Calcul du solde initial en tenant compte du type (DEBITEUR/CREDITEUR)
            $montantInitial = 0;
            if ($soldeInitial) {
                // Si CREDITEUR, le montant est positif (nous devons au fournisseur)
                // Si DEBITEUR, le montant est négatif (le fournisseur nous doit)
                $montantInitial = $soldeInitial->type === 'CREDITEUR' ? $soldeInitial->montant : -$soldeInitial->montant;
            }

            // Calcul du solde : Solde Initial + Factures - Règlements
            $fournisseur->solde = $montantInitial + ($fournisseur->total_factures ?? 0) - ($fournisseur->total_reglements ?? 0);
            return $fournisseur;
        });

    // Statistiques par mode de règlement pour un fournisseur spécifique
    $statsParMode = [];
    if ($request->fournisseur_id) {
        $modes = [
            ReglementFournisseur::MODE_ESPECE,
            ReglementFournisseur::MODE_CHEQUE,
            ReglementFournisseur::MODE_VIREMENT,
            ReglementFournisseur::MODE_DECHARGE,
            ReglementFournisseur::MODE_AUTRES
        ];

        foreach ($modes as $mode) {
            $statsParMode[$mode] = ReglementFournisseur::whereHas('facture', function($q) use ($request) {
                    $q->where('fournisseur_id', $request->fournisseur_id);
                })
                ->whereNotNull('validated_at')
                ->where('mode_reglement', $mode)
                ->sum('montant_reglement');
        }
    }

    // Détail des mouvements
    $mouvements = collect();
    if ($request->fournisseur_id) {
        $fournisseur = Fournisseur::with('soldeInitial')->find($request->fournisseur_id);
        $soldeInitial = $fournisseur->soldeInitial;

        // Ajout du solde initial aux mouvements s'il existe
        if ($soldeInitial) {
            $mouvements->push([
                'id' => $soldeInitial->id,
                'date' => $soldeInitial->date_solde,
                'type' => 'SOLDE_INITIAL',
                'reference' => 'SI-' . $soldeInitial->id,
                'debit' => $soldeInitial->type === 'CREDITEUR' ? $soldeInitial->montant : 0,
                'credit' => $soldeInitial->type === 'DEBITEUR' ? $soldeInitial->montant : 0,
                'commentaire' => $soldeInitial->commentaire
            ]);
        }

        // Récupération des factures avec leur point de vente et bon de commande
        $factures = FactureFournisseur::with(['pointVente', 'bonCommande'])
            ->where('fournisseur_id', $request->fournisseur_id)
            ->whereNotNull('validated_at')
            ->get()
            ->map(function ($facture) {
                return [
                    'id' => $facture->id,
                    'date' => $facture->date_facture,
                    'type' => 'FACTURE',
                    'reference' => $facture->code,
                    'bon_commande' => $facture->bonCommande?->code,
                    'point_vente' => $facture->pointVente->libelle,
                    'debit' => $facture->montant_ttc,
                    'credit' => 0,
                    'statut_paiement' => $facture->statut_paiement
                ];
            });

        // Récupération des règlements avec leur facture et point de vente
        $reglements = ReglementFournisseur::with('facture.pointVente')
            ->whereHas('facture', function ($q) use ($request) {
                $q->where('fournisseur_id', $request->fournisseur_id);
            })
            ->whereNotNull('validated_at')
            ->get()
            ->map(function ($reglement) {
                return [
                    'id' => $reglement->id,
                    'date' => $reglement->date_reglement,
                    'type' => 'REGLEMENT',
                    'reference' => $reglement->code,
                    'mode' => $reglement->mode_reglement,
                    'reference_paiement' => $reglement->reference_reglement,
                    'point_vente' => $reglement->facture->pointVente->libelle,
                    'debit' => 0,
                    'credit' => $reglement->montant_reglement
                ];
            });

        // Fusion et tri des mouvements
        $mouvements = $mouvements->concat($factures)->concat($reglements)->sortByDesc('date');
    }

    // Retour de la vue avec toutes les données
    return view('pages.rapports.achats.compte-fournisseur', [
        'fournisseurs' => $fournisseurs,
        'mouvements' => $mouvements,
        'solde_initial' => $request->fournisseur_id ? $soldeInitial : null,
        'statistiques' => [
            'total_fournisseurs' => $fournisseurs->count(),
            'total_factures' => $fournisseurs->sum('total_factures'),
            'total_reglements' => $fournisseurs->sum('total_reglements'),
            'solde_global' => $fournisseurs->sum('solde'),
            'fournisseurs_debiteurs' => $fournisseurs->where('solde', '<', 0)->count(),
            'fournisseurs_crediteurs' => $fournisseurs->where('solde', '>', 0)->count(),
            'montant_debiteur' => abs($fournisseurs->where('solde', '<', 0)->sum('solde')),
            'montant_crediteur' => $fournisseurs->where('solde', '>', 0)->sum('solde'),
            'par_mode' => $statsParMode
        ],
        'filtres' => [
            'fournisseurs' => Fournisseur::select('id', 'raison_sociale as nom')
                ->orderBy('raison_sociale')
                ->get(),
            'points_vente' => PointDeVente::select('id', 'nom_pv as libelle')
                ->orderBy('nom_pv')
                ->get()
        ],
        'params' => [
            'fournisseur_id' => $request->fournisseur_id,
            'point_de_vente_id' => $request->point_de_vente_id
        ]
    ]);
}
}
