<?php

namespace App\Http\Controllers;

use App\Models\Destockage;
use App\Http\Controllers\Controller;
use App\Http\Requests\DestockageRequest;
use App\Models\Catalogue\Article;
use App\Models\Parametre\Agent;
use App\Models\Parametre\Depot;
use App\Models\Vente\Client;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DestockageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Destockage::query()
            ->with("depot", "client", "lignes.article", "lignes.uniteMesure");

        // dépôt
        if ($request->filled("depot_id")) {
            $query->where("depot_id", $request->depot_id);
        }

        // client
        if ($request->filled("client_id")) {
            $query->whereHas("lignes", function ($query) use ($request) {
                return $query->where("client_id", $request->client_id);
            });
        }

        // article
        if ($request->filled("article_id")) {
            $query->whereHas("lignes", function ($query) use ($request) {
                return $query->where("article_id", $request->article_id);
            });
        }

        $depots = Depot::get(["id", "nom"]);
        $articles = Article::get(["id", "code_article"]);
        $clients = Client::get(["id", "raison_sociale"]);

        $destockages = $query->get();
        return view("vente.destockage.index", compact("destockages", "articles", "agents", "clients","depots"));
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
    public function store(DestockageRequest $request)
    {
        Log::debug("Entré des données de destockage :", ["data" => $request->all()]);
        try {
            DB::beginTransaction();

            Destockage::create($request->validated());

            DB::commit();

            Log::info("Destockage crée avec succès!");
            return response()
                ->back()
                ->with("message", "Destockage crée avec succès!");
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::debug("Erreure de validation lors de la creation du destockage");

            return response()
                ->back()
                ->with("errors", $e->errors());
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreure de validation lors de la creation du destockage", ["error" => $e->getMessage()]);

            return response()
                ->back()
                ->with("errors", $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Destockage $destockage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DestockageRequest $request, $destockage)
    {
        // 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DestockageRequest $request, Destockage $destockage)
    {
        Log::debug("Update des données de destockage :", ["data" => $request->all()]);

        try {
            DB::beginTransaction();

            $destockage->update($request->validated());

            DB::commit();

            Log::info("Destockage update avec succès!");

            return response()
                ->back()
                ->with("message", "Destockage modifié avec succès!");
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::debug("Erreure de validation lors de la modification du destockage");

            return response()
                ->back()
                ->with("errors", $e->errors());
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreure de validation lors de la modification du destockage", ["error" => $e->getMessage()]);

            return response()
                ->back()
                ->with("errors", $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Destockage $destockage)
    {
        Log::debug("Suppression des données de destockage :", ["data" => $request->all()]);

        try {
            DB::beginTransaction();

            $destockage->delete();

            DB::commit();

            Log::info("Destockage supprimé avec succès!");

            return response()
                ->back()
                ->with("message", "Destockage supprimé avec succès!");
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::debug("Erreure de validation lors de la suppression du destockage");

            return response()
                ->back()
                ->with("errors", $e->errors());
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreure de validation lors de la suppression du destockage", ["error" => $e->getMessage()]);

            return response()
                ->back()
                ->with("errors", $e->getMessage());
        }
    }
}
