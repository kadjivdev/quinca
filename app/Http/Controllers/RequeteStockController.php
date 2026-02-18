<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Catalogues\ArticleController;
use App\Models\RequeteStock;
use App\Http\Controllers\Controller;
use App\Http\Requests\RequeteStock as RequestsRequeteStock;
use App\Models\Catalogue\Article;
use App\Models\Parametre\Depot;
use App\Models\Parametre\UniteMesure;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Request;

class RequeteStockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $requetesQuery = RequeteStock::with(["article", "depot", "uniteMesure", "createdBy", "validatedBy"]);

        // query's conditions
        if ($request->get("filtre_article_id")) {
            $requetesQuery->where("article_id", $request->get("filtre_article_id"));
        }
        if ($request->get("filtre_depot_id")) {
            $requetesQuery->where("depot_id", $request->get("filtre_depot_id"));
        }

        // requetes
        $requetes = $requetesQuery->get();

        $articles = Article::with(["depots", "uniteMesure"])
            ->whereHas("depots")
            ->whereHas("uniteMesure")
            ->get(['id', 'code_article', 'designation', 'unite_mesure_id'])
            ->map(function ($article) {
                $article->depots = $article->depots->unique("id")->values();
                $article->unites = ArticleController::getUnites($article->id);
                return $article;
            });

        // return $articles;
        $depots = Depot::get(["id", "code_depot", "libelle_depot"]);
        $unites = UniteMesure::get(["id", "code_unite", "libelle_unite"]);

        return view("pages.rapports.ventes.requete.index", [
            "requetes" => $requetes,
            "articles" => $articles,
            "depots" => $depots,
            "unites" => $unites
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $articles = Article::get(["id", "code_article", "designation"]);
        $depots = Depot::get(["id", "code_depot", "libelle_depot"]);
        return view("pages.rapports.ventes.requete.create", [
            "articles" => $articles,
            "depots" => $depots,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RequestsRequeteStock $request)
    {
        try {
            DB::beginTransaction();
            $requete = RequeteStock::create($request->validated());
            DB::commit();
            Log::info("Requete de stock crée avec succès!", ["data" => $requete]);
            return redirect()
                ->back()
                ->with(["success" => "Requete de stock crée avec succès!"]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::debug("Erreure de validation lors de la création de la requete ", ["data" => $e->errors()]);
            DB::rollBack();
            return back()
                ->withErrors($e->errors());
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Erreure d'exception lors de la création de la requete", ["error" => $e->getMessage()]);
            return back()->withInput()->with("error", "Une erreure d'exception est survenue lors de la création de la requete");
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RequeteStock $requeteStock)
    {
        return view(
            "pages.rapports.ventes.requete.index",
            compact(["requete" => $requeteStock])
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RequeteStock $requeteStock)
    {
        $articles = Article::with("depots")
            ->has("depots")
            ->get(['id', 'code_article', 'designation'])
            ->map(function ($article) {
                $article->unites = ArticleController::getUnites($article->id);
                return $article;
            });
        $depots = Depot::get(["id", "code_depot", "libelle_depot"]);
        $unites = UniteMesure::get(["id", "code_unite", "libelle_unite"]);

        return view("pages.rapports.ventes.requete.edit", [
            "articles" => $articles,
            "depots" => $depots,
            "unites" => $unites,
            "requete" => $requeteStock,
        ]); //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RequestsRequeteStock $request, RequeteStock $requeteStock)
    {
        Log::debug("Début de modification d'une requete", ["data" => $request->all()]);

        try {
            DB::beginTransaction();
            Log::debug("Validated datas", ["data" => $request->validated()]);

            $requete = $requeteStock->update($request->validated());
            DB::commit();
            Log::info("Requete de stock modifiéee avec succès!", ["data" => $requete]);
            return redirect()
                ->route("requete_stock.index")
                ->with(["success" => "Requete de stock modifiéee avec succès!"]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::debug("Erreure de validation lors de la modification de la requete ", ["data" => $e->errors()]);
            DB::rollBack();
            return back()
                ->withErrors($e->errors());
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Erreure d'exception lors de la création de la requete", ["error" => $e->getMessage()]);
            return back()->withInput()->with("error", "Une erreure d'exception est survenue lors de la création de la requete");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequeteStock $requeteStock)
    {
        Log::debug("Suppression d'une requete ", ["data" => $requeteStock]);

        try {
            DB::beginTransaction();
            $requeteStock->delete();
            DB::commit();
            Log::info("Requête de stock supprimée avec succès!");
            return back()->with("success", "Requête supprimée avec succès!");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info("Erreure lors de la suppression de la requete");
            return back()->with("error", $e->getMessage());
        }
    }

    /**
     * Validation de la requete
     */
    public function validateRequete(RequeteStock $requeteStock)
    {
        Log::debug("Validation d'une requete ", ["data" => $requeteStock]);
        try {
            DB::beginTransaction();

            $requeteStock->update([
                'validated_by' => auth()->id(),
                'validated_at' => now(),
            ]);

            DB::commit();
            Log::info("Requête de stock validée avec succès!");
            return back()->with("success", "Requête de stock validée avec succès!");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("Une erreure est survenue lors de la validation de la requete", ["data" => $e->getMessage()]);
            return back()->with("error", $e->getMessage());
        }
    }
}
