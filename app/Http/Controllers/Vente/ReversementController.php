<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use App\Models\Parametre\Depot;
use App\Models\Vente\AcompteClient;
use App\Models\Vente\Reversement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ReversementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reversements = Reversement::with('depot')->get();
        $depots = Depot::all();
        return view('pages.ventes.reversement.index', compact([
            'reversements',
            'depots',
        ]));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $depots = Depot::all();
        return view('pages.ventes.requetes.create', compact('depots'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'date_recette' => 'required|date',
                'depot_id' => 'required|integer|exists:depots,id',
                'recette' => 'required|numeric',
                'depense' => 'nullable|numeric',
                'recette_to_reverse' => 'nullable|numeric',
                'montant_reversed' => 'nullable|numeric',
                'commentaire' => 'nullable|string',
                'preuve' => 'nullable|file|mimes:pdf,doc,docx,jpeg,png',
            ]);

            DB::beginTransaction();

            if ($request->hasFile('preuve')) {
                $fileName = $request->file('preuve')->getClientOriginalName();
                $request->file('preuve')->move('reversementFiles', $fileName);
                $validated['preuve'] = asset('reversementFiles/' . $fileName);
            }

            // Créer la requête
            Reversement::create($validated);

            DB::commit();
            return back()->withInput()->with("success", "Reversement enregistréavec succès");
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du reversement:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'reversement_data' => $request->all() // Pour le débogage
            ]);

            return back()->withInput()->withErrors($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Reversement $requete)
    {
        return view('pages.ventes.reversement.show', compact('reversement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reversement $reversement)
    {
        $depots = Depot::all();

        return view('pages.ventes.reversement.edit', compact('depots','reversement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $reversement = Reversement::findOrFail($id);

            $validated = $request->validate([
                'date_recette' => 'required|date',
                'depot_id' => 'required|integer|exists:depots,id',
                'recette' => 'nullable|numeric',
                'depense' => 'nullable|numeric',
                'recette_to_reverse' => 'nullable|numeric',
                'montant_reversed' => 'nullable|numeric',
                'commentaire' => 'nullable|string',
                'preuve' => 'nullable|file|mimes:pdf,doc,docx,jpeg,png',
            ]);

            // dd($request);
            if ($request->hasFile('preuve')) {
                $fileName = $request->file('preuve')->getClientOriginalName();
                $request->file('preuve')->move('reversementFiles', $fileName);
                $validated['preuve'] = asset('reversementFiles/' . $fileName);
            }else {
                $validated['preuve'] = $reversement->preuve;
            }

            $reversement->update($validated);

            DB::commit();
            return redirect()->route('reversements.index')->with('success', 'Reversement modifié avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('reversements.index')->with('error', 'Une erreur est survenue ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Reversement::findOrFail($id);

        Reversement::destroy($id);

        return back()->with("success", "Reversement supprimé avec succès!");
    }

    public function validateReversement($id)
    {
        $reversement = Reversement::findOrFail($id);

        try {
            DB::beginTransaction();

            $reversement->update([
                'validated_by' => Auth::user()->id,
                'validated_at' => now(),
            ]);

            DB::commit();
            return back()->with("success", "Revergement validé avec succès!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with("error", "Une erreure est survenue au cours de la validation");
        }
    }
}
