<?php

namespace Database\Seeders;

use App\Models\Zone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
            ["libelle" => "Zone BTP", "description" => "La zone des BTPs"],
            ["libelle" => "Zone Nord", "description" => "La zone des clients du nord"],
            ["libelle" => "Zone Sud & Centre", "description" => "La zone des clients du Sud & du Centre"],
        ];

        /** */
        Zone::insert($zones);
    }
}
