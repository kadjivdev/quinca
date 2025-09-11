<?php

namespace App\Http\Controllers\Achat;

use App\Http\Controllers\Controller;
use App\Models\Achat\AccompteFournisseur;
use App\Models\Achat\Fournisseur;
use App\Models\Achat\RequeteFournisseur;
use App\Models\Catalogue\Article;
use App\Models\Vente\Requete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RequeteFournisseurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requetes = RequeteFournisseur::with('fournisseur')->with('articles')->get();
        $fournisseurs = Fournisseur::get(["id", "code_fournisseur", "raison_sociale"]);
        $articles = Article::all();

        $requetesMax = RequeteFournisseur::withTrashed()->count() + 1;
        return view('pages.achat.requete.index', compact([
            'requetes',
            'fournisseurs',
            'articles',
            'requetesMax'
        ]));
    }

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        $fournisseurs = Fournisseur::get(["id", "code_fournisseur", "raison_sociale"]);
        $articles = Article::all();

        return view('pages.achat.requete.create', compact(['fournisseurs', 'articles']));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        try {
            $request->validate([
                'num_demande' => 'required|integer|unique:requete_fournisseurs,num_demande',
                'montant' => 'required',
                'date_demande' => 'required|date',
                'nature' => 'required|string',
                'mention' => 'required|string',
                'formulation' => 'required|string',
                'fournisseur_id' => 'required|string',
                // 'motif' => 'nullable|string',
                // 'articles' => 'required|array',
                // 'articles.*' => 'exists:articles,id',
                'fichier' => 'nullable|file|mimes:pdf,doc,docx,jpeg,png', // types de fichiers autorisés
            ]);

            DB::beginTransaction();

            $validated['fichier'] = "";
            if ($request->hasFile('fichier')) {
                $fileName = $request->file('fichier')->getClientOriginalName();
                $request->file('fichier')->move('requeteFiles', $fileName);
                $validated['fichier'] = asset("requeteFiles/" . $fileName);
            }

            $validated['user_id'] = auth()->user()->id;

            // Créer la requête
            $requete = RequeteFournisseur::create([
                'num_demande' => $request->num_demande,
                'montant' => $request->montant,
                'date_demande' => $request->date_demande,
                'nature' => $request->nature,
                'mention' => $request->mention,
                'formulation' => $request->formulation,
                'user_id' => auth()->user()->id,
                'fournisseur_id' => $request->fournisseur_id,
                'motif' => $request->motif,
                'motif_content' => $request->autre_motif,
                'fichier' => $request->hasFile('fichier') ? $validated['fichier'] : null,
            ]);


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
            \Log::error('Erreur lors de la création de la requete:', [
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
    public function show($requeteId)
    {
        $requete = RequeteFournisseur::findOrFail($requeteId);
        return view('pages.achat.requete.show', compact('requete'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($requeteId)
    {
        $requete = RequeteFournisseur::findOrFail($requeteId);
        $fournisseurs = Fournisseur::get(["id", "code_fournisseur", "raison_sociale"]);
        $articles = Article::all();
        return view('pages.achat.requete.edit', compact(['fournisseurs', 'articles', 'requete']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $requete = RequeteFournisseur::findOrFail($id);

            $request->validate([
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
            return redirect()->route('requetes-frs.index')->with('success', 'Requête modifiée avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('requetes-frs.index')->with('error', 'Une erreur est survenue ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $requete = RequeteFournisseur::findOrFail($id);
        $requete->articles()->detach();
        RequeteFournisseur::destroy($id);

        return back()->with("success", "Requête supprimée avec succès!");
    }

    public function validateRequete($id)
    {
        $requete = RequeteFournisseur::findOrFail($id);

        try {
            DB::beginTransaction();

            // dd($requete->fournisseur_id);fournisseur_id
            $requete->update([
                'validator' => Auth::user()->id,
                'validate_at' => now()
            ]);

            AccompteFournisseur::create([
                'date' => $requete->date_demande,
                'montant' =>  $requete->montant,
                'facture_id' => null,
                'fournisseur_id' => $requete->fournisseur_id,
                // 'user_id' => Auth::user()->id,
                'type_paiement' => 'virement',
                'requete_id' => $requete->id,
                'point_de_vente_id' => Auth::user()->point_de_vente_id,
                'statut' => AccompteFournisseur::STATUT_VALIDE,
                'validated_at' => now(),
                'validated_by' => auth()->id(),
                'created_by' => auth()->id()
            ]);

            DB::commit();
            return back()->with("success", "Requête validée avec succès!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with("error", "Une erreure est survenue au cours de la validation");
        }
    }
}
