<?php

namespace Database\Seeders;

use App\Models\Parametres\PointDeVente;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // public function run()
    // {
    //     $this->call([
    //         RoleAndPermissionSeeder::class,
    //         PointDeVenteSeeder::class,
    //         AdminUserSeeder::class,
    //     ]);
    // }

    public function run(): void
    {
        $this->call(PointDeVenteSeeder::class);
        $this->call(RoleAndPermissionSeeder::class);    // D'abord créer les permissions et rôles
        $this->call(AddPermissionSeeder::class); //Ajout de nouvelles permissions
        $this->call(AdminUserSeeder::class);         // Ensuite créer l'admin
    }
}
