<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use App\Models\Vente\Client;
use App\Models\Vente\Recouvrement as VenteRecouvrement;
use Illuminate\Http\Request;

class RecouvrementController extends Controller
{
    function index(Request $request)
    {
        // clients
        $clients = collect();
        Client::chunk(100, function ($chunk) use (&$clients) {
            $clients = $clients->merge($chunk); //merge the chunk
        });

        // recouvrements
        $recouvrements = collect();
        VenteRecouvrement::chunk(100, function ($chunk) use (&$recouvrements) {
            $recouvrements = $recouvrements->merge($chunk); //merge du chunk
        });

        if ($request->client) {
            $recouvrements = $recouvrements->where("client_id", $request->client);
        }

        // 
        return view("pages.ventes.client.recouvrements.index", compact("recouvrements", "clients"));
    }


    /**
     * Enregistrement d'un recouvrement
     */

    function store(Request $request)
    {
        $request->validate([
            "client_id" => ["required"],
            "comments" => ["required"]
        ], [
            "client_id" => "Le client est réquis",
            "comments" => "Le Commenataire est réquis",
        ]);

        $data = array_merge($request->all(), ["user_id" => auth()->user()->id]);
        VenteRecouvrement::create($data);

        return redirect()->back()->with("message", "Enregistrement éffectué avec succès!");
    }

    /**
     * Validation d'un recouvrement
     */

    function verification(Request $request)
    {
        $request->validate([
            "recouvrements" => ["required"],
        ], [
            "recouvrements.required" => "Choisissez au moins un recouvrement"
        ]);

        $recouvrements = VenteRecouvrement::whereIn("id", $request->recouvrements);
        $recouvrements->update([
            "verified" => true,
            "verified_by" => auth()->user()->id,
            "verified_at" => now(),
        ]);

        return redirect()->route("recouvrement.index")->with("message", "Recouvrement vérifié avec succès!");
    }
}
