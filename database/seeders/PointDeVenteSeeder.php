<?php

namespace Database\Seeders;
use App\Models\Parametre\PointDeVente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PointDeVenteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pv = PointDeVente::create([
            'nom_pv' => 'Cotonou',
            'code_pv' => 'POS-COT-8985',
            'adresse_pv' => '-',
        ]);
        $pv->save();
    }
}

