<?php

namespace App\Http\Controllers\Portail;


use App\Http\Controllers\Controller;
use App\Models\Securite\Role;
use Carbon\Carbon;
use Spatie\Permission\Models\Permission;

class PortailController extends Controller
{

    public function index()
    {
        
        // Configuration de la locale en français
        Carbon::setLocale('fr');

        // Formatage de la date
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY, HH:mm');

        // Retourne la vue avec la date
        return view('pages.portail.index', compact('date'));
    }
}
