<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use App\Models\Catalogue\Article;
use App\Models\Vente\AcompteClient;
use App\Models\Vente\Client;
use App\Models\Vente\Requete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
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
                'fichier' => 'nullable|file|mimes:pdf,doc,docx,jpeg,png', // types de fichiers autorisés
            ]);

            DB::beginTransaction();

            if ($request->hasFile('fichier')) {
                $filePath = $request->file('fichier')->store('uploads', 'public'); // Stocke le fichier dans le dossier 'uploads'
                $validated['fichier'] = $filePath;
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
                'fichier' => $request->hasFile('fichier') ? $request->file('fichier')->store('uploads', 'public') : null,
            ]);

            if ($request->motif == 'Articles') {
                $requete->articles()->attach($request->articles);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Requête enregistrée avec succès',
            ]);
            // return redirect()->route('requetes.index')->with('success', 'Requête enregistrée avec succès');
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la requete:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all() // Pour le débogage
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création du règlement: ' . $e->getMessage()
            ], 500);
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

        // dd($requete->articles->pluck("id"));

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
            return redirect()->route('requetes.index')->with('error', 'Une erreur est survenue '.$e->getMessage());
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

            AcompteClient::create([
                'date' => $requete->date_demande,
                'montant' =>  $requete->montant,
                'facture_id' => null,
                'client_id' => $requete->client_id,
                'user_id' => Auth::user()->id,
                'type_paiement' => 'virement',
                'requete_id' => $requete->id,
                'point_de_vente_id' => Auth::user()->point_de_vente_id
            ]);

            DB::commit();
            return back()->with("success", "Requête validée avec succès!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with("error", "Une erreure est survenue au cours de la validation");
        }
    }
}
