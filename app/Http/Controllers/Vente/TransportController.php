<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use App\Models\Catalogue\Article;
use App\Models\Vente\AcompteClient;
use App\Models\Vente\Client;
use App\Models\Vente\Transport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transports = Transport::with('client')->get();
        $clients = Client::all();
        $articles = Article::all();
        return view('pages.ventes.transport.index', compact([
            'transports',
            'clients',
            'articles'
        ]));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            $request->validate([
                'montant' => 'required|integer',
                'date_op' => 'required|date',
                'client_id' => 'required|string',
            ]);

            DB::beginTransaction();

            Transport::create([
                'montant' => $request->montant,
                'date_op' => $request->date_op,
                'client_id' => $request->client_id,
                'observation' => $request->observation
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Enregistrement éffectué avec succès!',
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création du règlement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transport = Transport::with('client')->findOrFail($id);

        return view('pages.ventes.transport.show', compact(['transport']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $transport = Transport::with('client')->findOrFail($id);
        $clients = Client::all();
        return view('pages.ventes.transport.edit', compact(['transport', 'clients']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'montant' => 'required|integer',
                'date_op' => 'required|date',
                'client_id' => 'required|string',
            ]);

            Transport::where('id', $id)->update([
                'montant' => $request->montant,
                'date_op' => $request->date_op,
                'client_id' => $request->client_id,
                'observation' => $request->observation
            ]);

            DB::commit();
            return redirect()->route('transports.index')->with("success", "Modification effectuée avec succès!");
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('transports.index')->with("error", "Opération échouée!");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Transport::destroy($id);

        return redirect()->route('transports.index')->with('success', 'Requête de transport supprimée avec succès');
    }

    public function validateRequete($id)
    {
        $transport = Transport::findOrFail($id);

        try {
            DB::beginTransaction();

            $transport->update([
                'validator' => Auth::user()->id,
                'validate_at' => now()
            ]);

            AcompteClient::create([
                'date' => $transport->date_op,
                'montant' =>  $transport->montant,
                'facture_id' => null,
                'client_id' => $transport->client_id,
                'user_id' => Auth::user()->id,
                'type_paiement' => 'virement',
                'transport_id' => $transport->id,
                'point_de_vente_id' => Auth::user()->point_de_vente_id
            ]);

            DB::commit();
            return back()->with("success","Requête de transport validée avec succès");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with("error","Opération échouée ".$e->getMessage());
        }
    }
}
