<?php

namespace App\Http\Controllers\Vente;

use App\Helpers\StringHelper;
use App\Http\Controllers\Controller;
use App\Models\Catalogue\Article;
use App\Models\Parametre\UniteMesure;
use App\Models\Vente\Client;
use App\Models\Vente\Devis;
use App\Models\Vente\DevisDetail;
use App\Models\Vente\PointVente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Codedge\Fpdf\Fpdf\ChiffreEnLettre;
use Codedge\Fpdf\Fpdf\PDF_MC_Table;
use Illuminate\Support\Facades\Validator;

class ProformaController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        // $this->middleware('permission:facture.proformas.create')->only(['create', 'store']);
        // $this->middleware('permission:facture.proformas.validate')->only(['valider']);
    }

    public function create()
    {
        $i = 1;
        // $devis = Devis::all();
        $devis = Devis::all();

        $clients = Client::all();
        $articles = Article::all();
        $unites_mesures = UniteMesure::all();
        return view('pages.ventes.facture.proforma.index', compact('devis', "clients", "articles", "unites_mesures"));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required',
            'date_pf' => 'required',
            'qte_cdes.*' => 'required',
            'articles.*' => 'required',
            'unites.*' => 'required',
            'prixUnits.*' => 'required',
        ]);


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // dd($request->all());

        $nbr = Devis::max('id');
        $lettres = strtoupper(substr(StringHelper::removeAccents(Auth::user()->name), 0, 3));
        DB::beginTransaction();

        try {
            $devis = Devis::create([
                'date_devis' => $request->date_pf,
                'statut' => 'Lancée',
                'client_id' => $request->client_id,
                'reference' => 'KAD-' . 'D' . ($nbr + 1) . '-' . date('dmY') . '-' . $lettres,
                'user_id' => Auth::user()->id,
            ]);

            $count = count($request->qte_cdes);
            for ($i = 0; $i < $count; $i++) {
                DevisDetail::create([
                    'qte_cmde' => $request->qte_cdes[$i],
                    'article_id' => $request->articles[$i],
                    'prix_unit' => $request->prixUnits[$i],
                    'unite_mesure_id' => $request->unites[$i],
                    'devis_id' => $devis->id,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Proforma enregistré avec succès.');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->with('error', 'Erreur enregistrement du proforma.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $devis = Devis::with(["details", "articles"])->find($id);
        return view('pages.ventes.facture.proforma.partials.show', compact('devis'));

        // return response()->json($devis);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $devis = Devis::with(["details", "articles"])->find($id);
        $clients = Client::all();
        $articles =  Article::all();
        $unites_mesures = UniteMesure::all();

        return view('pages.ventes.facture.proforma.partials.edit', compact('devis', 'clients', 'articles', 'unites_mesures'));
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, $id)
    {
        $item = Devis::find($id);
        $itemId = $item->id;

        $item->update($request->all());

        // SUPPRESSION DES DETAILS
        $item->details()->delete();

        $count = count($request->qte_cdes);
        for ($i = 0; $i < $count; $i++) {
            DevisDetail::updateOrCreate(
                [
                    'devis_id' => $itemId,
                    'article_id' => $request->articles[$i],
                ],
                [
                    'qte_cmde' => $request->qte_cdes[$i],
                    'prix_unit' => $request->prixUnits[$i],
                    'unite_mesure_id' => $request->unites[$i],
                ]
            );
        }

        return redirect()->route('proforma.create')->with('success', 'Proforma modifié avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(string $id)
    {
        $item = Devis::find($id);
        $item->delete();
        return redirect()->back()->with('success', 'Proforma supprimé avec succès.');
    }

    public function valider($id)
    {
        $item = Devis::find($id);
        $item->statut = 'Valide';
        $item->valideur_id = Auth::user()->id;
        $item->validated_at = now();
        $item->save();
        return redirect()->back()->with('success', 'Proforma validé avec succès.');
    }

    public function lignesDevis($id)
    {
        $articles = DB::table('devis_details')
            ->join('devis', 'devis_details.devis_id', '=', 'devis.id')
            ->join('articles', 'articles.id', '=', 'devis_details.article_id')
            ->join('unite_mesures', 'unite_mesures.id', '=', 'devis_details.unite_mesure_id')
            ->join('clients', 'devis.client_id', '=', 'clients.id') // Ajout de la jointure avec la table clients
            ->where('devis_id', $id)
            ->where('devis_details.qte_cmde', '>', 0)
            ->select(
                'devis_details.*',
                'articles.nom',
                'unite_mesures.unite',
                'clients.nom_client',
                'clients.id',
                'clients.seuil'
            )
            ->distinct()
            ->get();

        // dd($articles);

        return response()->json([
            'articles'  => $articles
        ]);
    }

    public function listArticlesPoint()
    {
        // Récupère les articles qui sont vendus dans le point de vente du user connecté
        $pointVendueId = Auth::user()->point_vente_id;
        $articles = PointVente::find($pointVendueId)
            ->articles()
            ->wherePivot('qte_stock', '>', 0)
            ->get();

        return response()->json([
            'articles'  => $articles
        ]);
    }

    public function pdf($id)
    {
        // $data = Facture::with(['articles'])->where('id', $id)->first()->toArray();
        $data = DB::table('devis_details')
            ->join('devis', 'devis.id', '=', 'devis_details.devis_id')
            ->join('clients', 'devis.client_id', '=', 'clients.id')
            ->join('articles', 'articles.id', '=', 'devis_details.article_id')
            ->join('unite_mesures', 'unite_mesures.id', '=', 'devis_details.unite_mesure_id')
            ->where('devis_id', $id)
            ->select(
                'devis_details.*',
                'articles.nom',
                'clients.nom_client',
                'unite_mesures.unite',
                'devis.*',
                DB::raw('SUM(devis_details.prix_unit * devis_details.qte_cmde) as total_amount')
            )
            ->distinct()
            ->get()->toArray();


        $pdf = Pdf::loadView('pdf.devis', compact('data'));
        $date =  date("Y-m-d");
        return $pdf->download($date . '.pdf');
    }

    public function generatePDF($id)
    {
        $devis = Devis::with('Client')->find($id);
        $ligne_devis =  DB::table('devis_details')
            ->join('articles', 'articles.id', '=', 'devis_details.article_id')
            ->where('devis_details.devis_id', $devis->id)
            ->select('*')
            ->get();

        // dd($ligne_devis);

        $pdf = new PDF_MC_Table();
        $pdf->AliasNbPages();  // To use the total number of pages
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        $pdf->Image("assets/img/logos/logo.jpeg", 150, 10, 50, 30);
        $pdf->Image("assets/img/logos/head_facture.jpg", 10, 10, 70, 30);

        $pdf->SetFont('', 'B', 10);
        $pdf->Text(150, 42, 'Cotonou, le ' . date("d m Y"));

        $pdf->SetFont('', 'B', 12);
        $pdf->Text(10, 55, 'Facture Proforma');
        $pdf->Text(10, 62, utf8_decode('N° ' . $devis->reference));
        $pdf->SetFont('', 'BU', 12);
        $pdf->Text(10, 69, utf8_decode('DESTINATION :'));
        $pdf->SetFont('', '', 12);
        $pdf->Text(45, 69, utf8_decode($devis->client->address));

        $pdf->Text(135, 80, 'Client : ');
        $pdf->SetFont('', 'B', 12);
        $pdf->Text(150, 80, $devis->client->nom_client);

        $pdf->SetXY(10, 85);
        $pdf->SetFont('', 'B', 12);
        $pdf->SetWidths(array(100, 20, 30, 40));
        $pdf->SetAligns(array('L', 'L', 'C', 'C', 'C', 'R'));
        $pdf->Row(array(utf8_decode('Désignation'), utf8_decode('Quantité en tonne'), utf8_decode('PU. HT (FCFA)'), utf8_decode('Montant HT (FCFA)')));

        $pdf->SetFont('', '', 12);
        $tot_ht = 0;
        foreach ($ligne_devis as $one_devis) {
            $pdf->Row(array($one_devis->designation, $one_devis->qte_cmde, $one_devis->prix_unit, number_format($one_devis->qte_cmde * $one_devis->prix_unit, 2, ',', ' ')));
            $tot_ht += $one_devis->qte_cmde * $one_devis->prix_unit;
        }


        $real_tht = $tot_ht / 1.19;
        $tva = $real_tht * 0.18;
        $aib = $real_tht * 0.01;
        $ttc = $tot_ht;
        $pdf->SetWidths(array(150, 40));
        $pdf->SetAligns(array('R', 'C'));
        $pdf->Row(array('TOTAL HT', number_format($real_tht, 2, ',', ' ')));

        $pdf->Row(array('TVA', number_format($tva, 2, ',', ' ')));
        $pdf->Row(array('AIB', number_format($aib, 2, ',', ' ')));
        $pdf->SetFont('', 'B', 12);
        $pdf->Row(array('TOTAL TTC', number_format($ttc, 2, ',', ' ')));

        $lettre = new ChiffreEnLettre;
        $prix_lettre = $lettre->Conversion($tot_ht);

        $pdf->SetFont('', 'B', 8);
        $pdf->CheckPageBreak(10);
        $pdf->Text($pdf->GetX(), $pdf->GetY() + 10, utf8_decode('Arrêté la présente facture sur la somme de : ' . $prix_lettre));
        $pdf->CheckPageBreak(40);
        $pdf->Text($pdf->GetX() + 30, $pdf->GetY() + 30, utf8_decode('NB : Les marchandises livrées ne sont ni reprises ni échangées. Merci pour la compréhension '));
        $pdf->Text($pdf->GetX() + 45, $pdf->GetY() + 40, utf8_decode('KADJIV SARL vous remercie de votre passage et espère vous revoir bientôt'));

        $pdf->SetXY(0, $pdf->GetY() + 40);
        $pdf->CheckPageBreak(55);
        $pdf->SetFont('', 'B', 10);
        $pdf->Text($pdf->GetX() + 150, $pdf->GetY() + 10, utf8_decode('Service Facturation'));
        $pdf->Image("assets/img/logos/proforma_sign.jpg", $pdf->GetX() + 120, $pdf->GetY() + 15, 70, 30);

        // Générer le nom de fichier unique pour le PDF
        $fileName = uniqid('proforma_', true) . '.pdf';

        // Stocker le PDF dans le système de fichiers temporaire
        // $tempFilePath = storage_path('app/temp/' . $fileName);
        if ($pdf->Output('I', $fileName)) {

            // return redirect()->route('devis.index')->with('success', 'Proforma enregistré avec succès.');
        }
    }
}
