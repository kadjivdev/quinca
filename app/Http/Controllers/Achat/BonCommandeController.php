<?php

namespace App\Http\Controllers\Achat;

use App\Models\Catalogue\Article;
use App\Models\Parametre\UniteMesure;
use App\Models\Achat\BonCommande;
use App\Models\Achat\LigneBonCommande;
use App\Models\Achat\ProgrammationAchat;
use App\Http\Controllers\Controller;
use App\Models\Stock\StockDepot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Codedge\Fpdf\Fpdf\ChiffreEnLettre;
use Codedge\Fpdf\Fpdf\PDF_MC_Table;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use TCPDF;

class BonCommandeController extends Controller
{
    /**
     * Affiche la liste des bons de commande
     */
    /**
     * Affiche la liste des bons de commande
     */
    public function index()
    {
        // Récupérer l'utilisateur connecté et son point de vente
        $user = auth()->user();
        if (!$user->point_de_vente_id) {
            return redirect()->back()->with('error', 'Vous n\'avez pas de point de vente associé à votre compte');
        }

        // Date au format français
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        // Requête principale avec filtrage par point de vente
        $bonCommandes = BonCommande::with([
            'pointVente',
            'fournisseur',
            'programmation',
            'lignes.article',
            'lignes.uniteMesure',
            'creator',
            'updater'
        ])
            ->where('point_de_vente_id', $user->point_de_vente_id)
            ->get();

        // Programmations validées du point de vente de l'utilisateur
        $programmationsValidees = ProgrammationAchat::whereNotNull('validated_at')
            ->where('point_de_vente_id', $user->point_de_vente_id)
            ->whereDoesntHave('bonCommande', function ($query) {
                $query->whereNotNull('validated_at'); // Exclure uniquement les bons validés
            })
            ->with(['pointVente', 'fournisseur'])
            ->orderBy('validated_at', 'desc')
            ->get();

        // Statistiques pour le point de vente spécifique
        $totalBonCommandes = BonCommande::where('point_de_vente_id', $user->point_de_vente_id)->count();
        $montantTotal = BonCommande::where('point_de_vente_id', $user->point_de_vente_id)->sum('montant_total');
        $montantMoyen = $totalBonCommandes > 0 ? $montantTotal / $totalBonCommandes : 0;
        $nombreFournisseurs = BonCommande::where('point_de_vente_id', $user->point_de_vente_id)
            ->distinct('fournisseur_id')
            ->count('fournisseur_id');

        // Statistiques mensuelles pour le point de vente
        $statsParMois = BonCommande::selectRaw('MONTH(date_commande) as mois, COUNT(*) as total, SUM(montant_total) as montant')
            ->where('point_de_vente_id', $user->point_de_vente_id)
            ->whereYear('date_commande', now()->year)
            ->groupBy('mois')
            ->get();

        $data = [
            'date' => $date,
            'bonCommandes' => $bonCommandes,
            'totalBonCommandes' => $totalBonCommandes,
            'montantTotal' => $montantTotal,
            'montantMoyen' => $montantMoyen,
            'nombreFournisseurs' => $nombreFournisseurs,
            'statsParMois' => $statsParMois,
            'fournisseurs' => \App\Models\Achat\Fournisseur::all(),
            'programmationsValidees' => $programmationsValidees,
            'bonCommandesJour' => BonCommande::where('point_de_vente_id', $user->point_de_vente_id)
                ->whereDate('date_commande', Carbon::today())
                ->count(),
            'montantJour' => BonCommande::where('point_de_vente_id', $user->point_de_vente_id)
                ->whereDate('date_commande', Carbon::today())
                ->sum('montant_total'),
            'bonCommandesSemaine' => BonCommande::where('point_de_vente_id', $user->point_de_vente_id)
                ->whereBetween('date_commande', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->count(),
            'montantSemaine' => BonCommande::where('point_de_vente_id', $user->point_de_vente_id)
                ->whereBetween('date_commande', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->sum('montant_total'),
        ];

        return view('pages.achat.bon-commande.index', $data);
    }
    /**
     * Enregistre un nouveau bon de commande
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

            // Validation des données de base
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'code' => 'required|unique:bon_commandes,code',
                'date_commande' => 'required|date',
                'programmation_id' => 'required|exists:programmation_achats,id',
                'commentaire' => 'nullable|string',
                'cout_transport' => 'nullable|integer',
                'cout_chargement' => 'nullable|integer',
                'autre_cout' => 'nullable|integer',
                'lignes' => 'required|array|min:1',
                'lignes.*.article_id' => 'required|exists:articles,id',
                'lignes.*.prix_unitaire' => 'required|numeric|min:0',
                'lignes.*.quantite' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Récupérer la programmation et ses lignes
            $programmation = ProgrammationAchat::with('lignes')->findOrFail($validated['programmation_id']);

            // if (!$programmation->depot) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => "Cette programmation n'appartient à aucun dépôt",
            //     ]);
            // }

            // Création du bon de commande
            $bonCommande = BonCommande::create([
                'code' => $validated['code'],
                'date_commande' => $validated['date_commande'],
                'programmation_id' => $validated['programmation_id'],
                'point_de_vente_id' => $user->point_de_vente_id,
                'fournisseur_id' => $programmation->fournisseur_id,
                'commentaire' => $validated['commentaire'] ?? null,
                'montant_total' => 0,
                'cout_transport' => $validated['cout_transport'],
                'cout_chargement' => $validated['cout_chargement'],
                'autre_cout' => $validated['autre_cout'],
            ]);

            // Création des lignes
            foreach ($validated['lignes'] as $ligne) {
                // Trouver la ligne de programmation correspondante pour obtenir l'unite_mesure_id
                $ligneProgrammation = $programmation->lignes
                    ->where('article_id', $ligne['article_id'])
                    ->first();

                if (!$ligneProgrammation) {
                    throw new \Exception('Article non trouvé dans la programmation');
                }

                $ligneBonCommande = new LigneBonCommande();
                $ligneBonCommande->article_id = $ligne['article_id'];
                $ligneBonCommande->unite_mesure_id = $ligneProgrammation->unite_mesure_id; // Utilisation de l'unité de mesure de la programmation
                $ligneBonCommande->quantite = $ligne['quantite'];
                $ligneBonCommande->prix_unitaire = $ligne['prix_unitaire'];
                $ligneBonCommande->bon_commande_id = $bonCommande->id;
                $ligneBonCommande->save();


                // // On ajoute les quantités saisies au stock des articles
                // $stock = StockDepot::where(["depot_id" => $programmation->depot, "article_id" => $ligne['article_id']])->first();

                // if ($stock) {
                //     $stock->update(["quantite_reelle" => $stock->quantite_reelle + $ligne['quantite']]);
                // }
            }

            // Mise à jour du montant total
            $bonCommande->updateMontantTotal();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bon de commande créé avec succès',
                'data' => $bonCommande
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du bon de commande', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du bon de commande: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affiche les détails d'un bon de commande
     */
    public function show(BonCommande $bonCommande)
    {
        $bonCommande->load(['pointVente', 'fournisseur', 'lignes.article', 'lignes.uniteMesure', 'programmation']);

        return response()->json([
            'success' => true,
            'data' => $bonCommande
        ]);
    }

    /**
     * Met à jour un bon de commande
     */
    public function update(Request $request, BonCommande $bonCommande)
    {
        try {
            DB::beginTransaction();

            // Validation des données
            $validated = $request->validate([
                'date_commande' => 'required|date',
                'commentaire' => 'nullable|string',
                'cout_transport' => 'nullable|integer',
                'cout_chargement' => 'nullable|integer',
                'autre_cout' => 'nullable|integer',
                'lignes' => 'required|array|min:1',
                'lignes.*.article_id' => 'required|exists:articles,id',
                'lignes.*.prix_unitaire' => 'required|numeric|min:0',
                'lignes.*.quantite' => 'required|numeric|min:0',
                // 'lignes.*.taux_remise' => 'required|numeric|between:0,100',
            ]);

            // Mise à jour du bon de commande
            $bonCommande->update([
                'date_commande' => $validated['date_commande'],
                'commentaire' => $validated['commentaire'] ?? null,
                'cout_transport' => $validated['cout_transport'],
                'cout_chargement' => $validated['cout_chargement'],
                'autre_cout' => $validated['autre_cout'],
            ]);

            // Suppression des anciennes lignes
            $bonCommande->lignes()->delete();

            $programmation = ProgrammationAchat::with('lignes')->findOrFail($bonCommande->programmation_id);

            // Création des nouvelles lignes
            foreach ($validated['lignes'] as $ligne) {

                // Trouver la ligne de programmation correspondante pour obtenir l'unite_mesure_id
                $ligneProgrammation = $programmation->lignes
                    ->where('article_id', $ligne['article_id'])
                    ->first();

                $ligneBonCommande = new LigneBonCommande();
                $ligneBonCommande->article_id = $ligne['article_id'];
                $ligneBonCommande->prix_unitaire = $ligne['prix_unitaire'];
                $ligneBonCommande->unite_mesure_id = $ligneProgrammation->unite_mesure_id; // Utilisation de l'unité de mesure de la programmation
                $ligneBonCommande->quantite = $ligne['quantite'];
                $ligneBonCommande->bon_commande_id = $bonCommande->id;
                $ligneBonCommande->save();
            }

            // Mise à jour du montant total
            $bonCommande->updateMontantTotal();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bon de commande mis à jour avec succès',
                'data' => $bonCommande
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du bon de commande', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du bon de commande: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime un bon de commande
     */
    public function destroy(BonCommande $bonCommande)
    {
        try {
            DB::beginTransaction();

            $bonCommande->lignes()->delete();
            $bonCommande->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bon de commande supprimé avec succès'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du bon de commande', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du bon de commande: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Génère un nouveau code
     */
    public function generateCode()
    {
        try {
            // Format: BC-DATE-USR-XXXXX
            $date = now()->format('Ymd');
            $userInitials = substr(strtoupper(auth()->user()->name), 0, 3);

            do {
                $uuid = \Illuminate\Support\Str::uuid();
                $hash = md5($uuid . auth()->id() . time());
                $uniqueId = strtoupper(substr($hash, 0, 5));
                $newCode = "BC-{$date}-{$userInitials}-{$uniqueId}";
                $exists = BonCommande::where('code', $newCode)->exists();
            } while ($exists);

            return response()->json([
                'success' => true,
                'code' => $newCode
            ]);
        } catch (Exception $e) {
            Log::error('Erreur lors de la génération du code', [
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
     * Filtre les bons de commande
     */
    public function filter(Request $request)
    {
        $query = BonCommande::with(['pointVente', 'fournisseur', 'programmation']);

        // Filtre par période
        if ($request->has('period')) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('date_commande', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('date_commande', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('date_commande', Carbon::now()->month);
                    break;
            }
        }

        // Tri par montant
        if ($request->has('sort') && $request->has('order')) {
            if ($request->sort === 'montant') {
                $query->orderBy('montant_total', $request->order);
            }
        }

        $bonCommandes = $query->paginate(12);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $bonCommandes
            ]);
        }

        return redirect()->back();
    }

    /**
     * Récupère les articles d'un bon de commande
     */
    public function getArticles(BonCommande $bonCommande)
    {
        try {
            $articles = $bonCommande->lignes()
                ->with(['article', 'uniteMesure'])
                ->get()
                ->map(function ($ligne) {
                    return [
                        'id' => $ligne->article->id,
                        'reference' => $ligne->article->code_article,
                        'designation' => $ligne->article->designation,
                        'unite_mesure' => $ligne->uniteMesure->libelle_unite,
                        'unite_mesure_id' => $ligne->unite_mesure_id,
                        'quantite' => $ligne->quantite,
                        'prix_unitaire' => $ligne->prix_unitaire,
                        'montant_ligne' => $ligne->montant_ligne,
                        'taux_remise' => $ligne->taux_remise
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $articles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des articles',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Valide une programmation
     */
    public function validated(BonCommande $bonCommande)
    {
        try {

            // foreach ($bonCommande->lignes as $ligne) {
            //     // On ajoute les quantités saisies au stock des articles
            //     $stock = StockDepot::where(["depot_id" => $bonCommande->programmation->depot, "article_id" => $ligne->article_id])->first();

            //     if ($stock) {
            //         $stock->update(["quantite_reelle" => $stock->quantite_reelle + $ligne->quantite]);
            //     }
            // }

            if ($bonCommande->validate()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bon de commande validé avec succès'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Impossible de valider le Bon de commande'
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rejectBonCommande(Request $request, $id)
    {
        $bonCommande = BonCommande::findorFail($id);

        $request->validate([
            'motif_rejet' => 'required|string'
        ]);

        $bonCommande->motif_rejet = $request->motif_rejet;
        $bonCommande->rejected_by = Auth::id();
        $bonCommande->rejected_at = now();

        $bonCommande->save();

        return response()->json([
            'success' => true,
            'message' => 'Bon commande rejetée avec succès',
            'data' => $bonCommande
        ]);
    }

    public function generatePDF($id, $bon_object)
    {
        $bcde = BonCommande::with(['fournisseur'])->where('id', $id)->first();

        // dd($bcde->fournisseur); test
        $pdf = new PDF_MC_Table();
        $pdf->AliasNbPages();  // To use the total number of pages
        // $pdf->setLanguageArray($pdf->getLanguageArray('fr'));
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Définir la police à "DejaVu Sans" qui supporte les caractères UTF-8
        // $pdf->SetFont('DejaVu Sans', '', 12); // Ou 'dejavusans' pour une version UTF-8

        $pdf->Image("assets/img/logos/logo.jpeg", 150, 10, 50, 30);
        $pdf->Image("assets/img/logos/head_facture.jpg", 10, 10, 70, 30);

        $date = Carbon::now();
        $date->locale('fr');

        $pdf->SetFont('', 'B', 10);
        $dateText = Carbon::now()->locale('fr')->isoFormat('D MMMM YYYY');

        $pdf->Text(150, 42, 'Cotonou, le ' . utf8_decode($dateText));

        $pdf->SetFont('', 'B', 12);
        // $pdf->Text(10, 55, 'FOURNISSEUR');
        $pdf->SetFont('', 'B', 12);
        $pdf->Text(10, 62, utf8_decode('FOURNISSEUR : ' . $bcde->fournisseur->raison_sociale));
        $pdf->SetFont('', 'B', 12);
        $pdf->Text(10, 69, utf8_decode("OBJET : $bon_object"));

        // $pdf->Text(13, 80, 'Client : '.$devis->client->nom_client);
        $pdf->SetXY(10, 73);
        $pdf->MultiCell(190, 15, utf8_decode('BON DE COMMANDE : ' . $bcde->code), '', 'C');

        $pdf->SetXY(10, 85);
        $pdf->SetFont('', 'B', 12);
        $pdf->SetWidths(array(100, 20, 30, 40));
        $pdf->SetAligns(array('L', 'C', 'R', 'R'));
        $pdf->Row(array(utf8_decode('Désignation'), utf8_decode('Quantité'), utf8_decode('PU'), utf8_decode('Montant')));

        $ligne_commandes = DB::table('ligne_bon_commandes')
            // ->with("article")
            ->join('articles', 'articles.id', '=', 'ligne_bon_commandes.article_id')
            ->where('ligne_bon_commandes.bon_commande_id', $bcde->id)
            ->select('*')
            ->get();

        $tot_ht = 0;
        foreach ($ligne_commandes  as $ligne_commande) {
            $art = Article::find($ligne_commande->article_id);

            $pdf->Row(array($art->designation, number_format($ligne_commande->quantite, 2, ',', ' '), number_format($ligne_commande->prix_unitaire, 2, ',', ' '), number_format($ligne_commande->quantite * $ligne_commande->prix_unitaire, 2, ',', ' ')));
            $tot_ht += $ligne_commande->quantite * $ligne_commande->prix_unitaire;
        }

        $pdf->SetWidths(array(150, 40));
        $pdf->SetAligns(array('C', 'R'));
        $pdf->Row(array('TOTAL', number_format($tot_ht, 2, ',', ' ')));

        $lettre = new ChiffreEnLettre;
        $prix_lettre = $lettre->Conversion($tot_ht);

        $pdf->SetFont('', 'B', 10);
        $pdf->CheckPageBreak(10);
        $pdf->Text($pdf->GetX(), $pdf->GetY() + 10, utf8_decode('Arrêté le présent bon de commande à la somme de : ' . $prix_lettre));

        $pdf->CheckPageBreak(45);
        $pdf->SetFont('', 'B', 10);
        $pdf->Text($pdf->GetX() + 147, $pdf->GetY() + 45, utf8_decode('LA DIRECTRICE GENERALE'));
        $pdf->Text($pdf->GetX() + 150, $pdf->GetY() + 75, utf8_decode('Kadidjatou A. DJAOUGA'));

        // Générer le nom de fichier unique pour le PDF
        $fileName = uniqid('Bon_Commande_', true) . '.pdf';

        // Stocker le PDF dans le système de fichiers temporaire
        // $tempFilePath = storage_path('app/temp/' . $fileName);
        if ($pdf->Output('I', $fileName)) {

            // return redirect()->route('devis.index')->with('success', 'Proforma enregistré avec succès.');
        }
    }
}
