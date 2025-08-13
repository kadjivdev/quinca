<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use App\Models\Catalogue\Article;
use App\Models\Vente\AcompteClient;
use App\Models\Vente\Client;
use App\Models\Vente\CompteClient;
use App\Models\Vente\Requete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RequeteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requetes = Requete::with('client')->with('articles')->get();
        $clients = Client::all();
        $articles = Article::all();
        return view('pages.ventes.requete.index', compact([
            'requetes',
            'clients',
            'articles',
        ]));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::all();
        $articles = Article::all();

        return view('pages.ventes.requetes.create', compact(['clients', 'articles']));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $request->validate([
                'num_demande' => 'required|integer',
                'montant' => 'required',
                'date_demande' => 'required|date',
                'nature' => 'required|string',
                'mention' => 'required|string',
                'formulation' => 'required|string',
                'client_id' => 'required|string',
                'motif' => 'required|string',
                // 'articles' => 'required|array',
                'articles.*' => 'exists:articles,id',
                '_fichier' => 'nullable|file|mimes:pdf,doc,docx,jpeg,png', // types de fichiers autorisés
            ]);

            DB::beginTransaction();

            if ($request->hasFile('_fichier')) {
                $fileName = $request->file('_fichier')->getClientOriginalName();
                $request->file('_fichier')->move('requeteFiles', $fileName);
                $validated['fichier'] = asset('requeteFiles/' . $fileName);
            }
            
            // Créer la requête
            $requete = Requete::create([
                'num_demande' => $request->num_demande,
                'montant' => $request->montant,
                'date_demande' => $request->date_demande,
                'nature' => $request->nature,
                'mention' => $request->mention,
                'formulation' => $request->formulation,
                'user_id' => Auth()->user()->id,
                'client_id' => $request->client_id,
                'motif' => $request->motif,
                'motif_content' => $request->autre_motif,
                'fichier' => $request->hasFile('_fichier') ? $validated['fichier'] : null,
            ]);

            if ($request->motif == 'Articles') {
                $requete->articles()->attach($request->articles);
            }

            DB::commit();
            return back()->withInput()->with("success", "Requête enregistrée avec succès");
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la requete:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all() // Pour le débogage
            ]);

            return back()->withInput()
                ->with("error", 'Une erreur est survenue lors de la création du règlement: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Requete $requete)
    {
        return view('pages.ventes.requete.show', compact('requete'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Requete $requete)
    {
        $clients = Client::all();
        $articles = Article::all();

        return view('pages.ventes.requete.edit', compact(['clients', 'articles', 'requete']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $requete = Requete::findOrFail($id);

            $validated = $request->validate([
                'num_demande' => 'required|integer',
                'date_demande' => 'required|date',
                'nature' => 'required|string',
                'mention' => 'required|string',
                'formulation' => 'required|string',
                'client_id' => 'required|string',
                // 'articles' => 'required|array',
                'articles.*' => 'exists:articles,id',
            ]);

            $requete->update([
                'num_demande' => $request->num_demande,
                'montant' => $request->montant,
                'date_demande' => $request->date_demande,
                'nature' => $request->nature,
                'mention' => $request->mention,
                'formulation' => $request->formulation,
                'client_id' => $request->client_id,
                'motif' => $request->motif,
                'motif_content' => $request->autre_motif,
            ]);

            $requete->articles()->sync($request->articles);

            DB::commit();
            return redirect()->route('requetes.index')->with('success', 'Requête modifiée avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('requetes.index')->with('error', 'Une erreur est survenue ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $requete = Requete::findOrFail($id);
        $requete->articles()->detach();
        Requete::destroy($id);

        return back()->with("success", "Requête supprimée avec succès!");
    }

    public function validateRequete($id)
    {
        $requete = Requete::findOrFail($id);

        try {
            DB::beginTransaction();

            $requete->update([
                'validator' => Auth::user()->id,
                'validate_at' => now()
            ]);

            $acompte = AcompteClient::create([
                'date' => $requete->date_demande,
                'montant' =>  $requete->montant,
                'facture_id' => null,
                'client_id' => $requete->client_id,
                'user_id' => Auth::user()->id,
                'type_paiement' => 'virement',
                'requete_id' => $requete->id,
                'statut' => AcompteClient::STATUT_VALIDE,
                'created_by' => auth()->user()->id,
                'validated_at' => now(),
                'validated_by' => auth()->user()->id,
                'point_de_vente_id' => Auth::user()->point_de_vente_id
            ]);

            $acompte->compteClient()->create([
                'date_op' => $acompte->date,
                'montant_op' => $acompte->montant,
                'client_id' => $acompte->client_id,
                'user_id' => Auth::user()->id,
                'type_op' => 'AC_CLT',
            ]);

            DB::commit();
            return back()->with("success", "Requête validée avec succès!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with("error", "Une erreure est survenue au cours de la validation");
        }
    }
}
