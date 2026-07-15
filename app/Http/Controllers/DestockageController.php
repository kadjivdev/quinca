<?php

namespace App\Http\Controllers;

use App\Models\Destockage;
use App\Http\Controllers\Controller;
use App\Http\Requests\DestockageRequest;
use App\Models\Catalogue\Article;
use App\Models\Parametre\Depot;
use App\Models\Parametre\UniteMesure;
use App\Models\Stock\StockDepot;
use App\Models\Vente\Client;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ServiceStockEntree;

class DestockageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Destockage::with([
            "depot",
            "createdBy",
            "validatedBy",
            "client",
            "lignes",
            "lignes.article",
            "lignes.uniteMesure"
        ]);

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

        $depots = Depot::get(["id", "libelle_depot"]);
        $articles = Article::get(["id", "code_article", "designation"]);
        $clients = Client::get(["id", "raison_sociale"]);
        $uniteMesures = UniteMesure::get(["id", "libelle_unite"]);

        $destockages = $query->get();
        $stacks = [
            "destockagesCount" => $destockages->count(),
            "destockagesToday" => $destockages->where('date_op', now()->format('Y-m-d'))->count(),
            "totalAmount" => $destockages->flatMap->lignes?->sum("montant") ?? 0,
            "destockagesMonth" => $destockages->whereBetween('date_op', [now()->startOfMonth(), now()->endOfMonth()])
                ->flatMap
                ->lignes?->sum('montant') ?? 0,
            "totalAmountToday" => $destockages->where('date_op', now()->format('Y-m-d'))
                ->flatMap
                ->lignes?->sum('montant') ?? 0,
        ];

        return view("pages.ventes.destockage.index", compact("destockages", "articles", "clients", "depots", "stacks", "uniteMesures"));
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

            $destockage = Destockage::create($request->validated());

            $serviceStockEntree =  new ServiceStockEntree();
            $depot = Depot::findOrFail($request->depot_id);

            // On verifie si les quantités saisies au niveau des articles ne depasse pas le reste de quantité sur l'article
            foreach ($request->lignes as $ligne) {

                // 
                $stock = StockDepot::query()
                    ->where('depot_id', $depot->id)
                    ->where('article_id', $ligne['article_id'])
                    ->first();

                /**
                 * Recherche de la conversion
                 */
                $venteUnite = UniteMesure::findOrFail($ligne['unite_mesure_id']);
                $article = Article::findOrFail($ligne['article_id']);

                $conversion = $serviceStockEntree
                    ->rechercherConversion(
                        $ligne['unite_mesure_id'],
                        $article->unite_mesure_id, // $stock->unite_mesure_id,
                        $stock->article_id
                    );

                if (!$conversion) {
                    return response()->json([
                        'status' => false,
                        'message' => "Il n'y a pas de conversion de l'unité ($venteUnite->libelle_unite) vers ({{$article->uniteMesure?->libelle_unite}}) pour l'article ($article->code_article), ni l'inverse! Veuillez créer cette conversion afin de continuer l'opération"
                    ], 500);
                }

                /**Qte de Base */
                $qantiteBase = $stock->quantite_reelle;

                /**Qte de requete */
                $stock->qantiteRequete = $stock->quantite_requete;

                /**Qte Vendue */
                $qteTotalVendu = $stock->article->qteVendu($stock->depot_id);

                /**Qte Reste */
                $resteStock = ($qantiteBase + $stock->qantiteRequete) - $qteTotalVendu; //$article->reste($stock->depot_id);

                Log::debug("article :", ["data" => $article->code_article]);
                Log::debug("qantiteBase :", ["data" => $qantiteBase]);
                Log::debug("qantiteRequete :", ["data", $stock->qantiteRequete]);
                Log::debug("qteTotalVendu :", ["data", $qteTotalVendu]);
                Log::debug("resteStock :", ["data", $resteStock]);

                /**
                 * Obtention de la quantité convertie
                 */

                // unite de vente vers unite de base de l'article
                $QteConvertie = $serviceStockEntree
                    ->convertirQuantite($ligne['qte'], $conversion, $ligne['unite_mesure_id']);

                // on verifie la quantité restante de l'article dans le depot est suffisante
                if ($resteStock < $QteConvertie) {
                    throw new Exception("Le reste du stock de l'article ($article->designation) est de $resteStock {{$article->uniteMesure?->libelle_unite}} dans le depôt ({$stock->depot?->libelle_depot})! Vous avez saisi $QteConvertie {{$article->uniteMesure?->libelle_unite}}!  Stock insuiffisant par rapport à la quantité saisie");
                }
            }


            $destockage->lignes()
                ->createMany($request->validated()["lignes"]);

            DB::commit();

            Log::info("Destockage crée avec succès!");
            return redirect()
                ->back()
                ->with("message", "Destockage crée avec succès!");
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::debug("Erreure de validation lors de la creation du destockage");

            return redirect()
                ->back()
                ->withErrors($e->errors());
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreure de validation lors de la creation du destockage", ["error" => $e->getMessage()]);

            return redirect()
                ->back()
                // ✅ Utiliser withErrors() à la place
                ->withErrors($e->getMessage());
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

            // update of the reference
            $destockage->update([
                "reference" => "__$destockage->reference"
            ]);

            // suppression des lignes
            $destockage->lignes()
                ->delete();

            // suppression du destockage
            $destockage->delete();

            DB::commit();

            Log::info("Destockage supprimé avec succès!");

            return redirect()
                ->back()
                ->with("message", "Destockage supprimé avec succès!");
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::debug("Erreure de validation lors de la suppression du destockage");

            return redirect()
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

    /**
     * Validate the specified resource from storage.
     */
    public function validateDestockage(Destockage $destockage)
    {
        Log::debug("Validation des données de destockage :", ["data" => $destockage]);

        try {
            DB::beginTransaction();

            $destockage->update([
                "validated_by" => Auth::id(),
                "validated_at" => now(),
            ]);

            DB::commit();

            Log::info("Destockage validé avec succès!");

            return redirect()
                ->back()
                ->with("message", "Destockage validé avec succès!");
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::debug("Erreure de validation lors de la suppression du destockage");

            return redirect()
                ->back()
                ->with("errors", $e->errors());
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Erreure de validation lors de la suppression du destockage", ["error" => $e->getMessage()]);

            return redirect()
                ->back()
                ->with("errors", $e->getMessage());
        }
    }
}
