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
                fn($q) => $q->whereNotNull('validated_at'),
                fn($q) => $q->whereNull('validated_at')
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
                fn($q) => $q->whereNotNull('validated_at'),
                fn($q) => $q->whereNull('validated_at')
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
                fn($q) => $q->whereNotNull('validated_at'),
                fn($q) => $q->whereNull('validated_at')
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
                fn($q) => $q->whereNotNull('validated_at'),
                fn($q) => $q->whereNull('validated_at')
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

    public function rapportCompteFournisseur(Request $request)
    {
        $fournisseurs = Fournisseur::orderBy('created_at', 'desc');

        // Construction de la requête principale
        $query = Fournisseur::query();

        // Filtre par fournisseur si spécifié
        if ($request->fournisseur_id) {
            $query->where('id', $request->fournisseur_id);
        }

        // Filtre par point de vente si spécifié
        if ($request->point_de_vente_id) {
            $query->whereHas('factures', function ($q) use ($request) {
                $q->where('point_de_vente_id', $request->point_de_vente_id);
            });
        }

        $fournisseurs = $query->get();
        // Récupération et calcul des soldes
        $fournisseurs->map(function ($fournisseur) {
            $fournisseur->totalAppro = $fournisseur->approvisionnements->sum("montant");
            $fournisseur->solde = $fournisseur->reste_solde();

            $fournisseur->factureAchatAmount = $fournisseur->facture_fournisseurs->sum("montant_ttc");

            $fournisseur->reglementsAmount = $fournisseur->facture_fournisseurs->sum(function ($query) {
                return $query->facture_reglements_amount();
            });

            $fournisseur->resteAsolder = $fournisseur->factureAchatAmount - $fournisseur->reglementsAmount;
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
                $statsParMode[$mode] = ReglementFournisseur::whereHas('facture', function ($q) use ($request) {
                    $q->where('fournisseur_id', $request->fournisseur_id);
                })
                    ->whereNotNull('validated_at')
                    ->where('mode_reglement', $mode)
                    ->sum('montant_reglement');
            }
        }

        // Retour de la vue avec toutes les données
        return view('pages.rapports.achats.compte-fournisseur', [
            'fournisseurs' => $fournisseurs,
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
