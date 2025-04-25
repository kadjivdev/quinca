<?php

namespace App\Http\Controllers\Achat;

use App\Http\Controllers\Controller;
use App\Models\Achat\Fournisseur;
use App\Models\Achat\FournisseurApprovisionnement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FournisseurApprovisionnementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $approvisionnements = FournisseurApprovisionnement::with(["fournisseur", "actor"])->orderBy("id", "desc")->get();
        $fournisseurs = Fournisseur::all();

        return view("pages.achat.approvisionnement.index", compact("approvisionnements", "fournisseurs"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (request()->ajax()) {
            // VALIDATION
            $validator = Validator::make(FournisseurApprovisionnement::rules(), FournisseurApprovisionnement::messages());

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                ]);
            }

            return response()->json([
                "success" => true,
            ]);
        } else {
            # code...
            $validated = $request->validate(FournisseurApprovisionnement::rules(), FournisseurApprovisionnement::messages());

            if ($request->hasFile("document")) {
                $document = $request->file("document");
                $name = $document->getClientOriginalName();
                $document->move("files", $name);
                $document_url = asset("files/" . $name);
                $data = array_merge($validated, ["document" => $document_url]);
            }

            FournisseurApprovisionnement::create($data);

            return redirect()->back()->with("success", "Approvisionnement éffectué avec succès!");
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $fournisseur = FournisseurApprovisionnement::findOrFail($id);
        $fournisseur->delete();
        return redirect()->back()->with("success", "Approvisionnement supprimé avec succès!");
    }

    // Rejet d'approvisionnement
    public function rejeter(Request $request, $id)
    {
        $fournisseur = FournisseurApprovisionnement::findOrFail($id);
        $fournisseur->update([
            "rejected_by" => auth()->user()->id
        ]);

        return redirect()->back()->with("success", "Approvisionnement rejeté avec succès!");
    }

    // Valider un approvisionnement
    public function valider(Request $request, $id)
    {
        $fournisseur = FournisseurApprovisionnement::findOrFail($id);
        $fournisseur->update([
            "validated_by" => auth()->user()->id,
            "validated_at" => now(),
        ]);

        return redirect()->back()->with("success", "Approvisionnement validé avec succès!");
    }
}
