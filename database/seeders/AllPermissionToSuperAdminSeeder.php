<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AllPermissionToSuperAdminSeeder extends Seeder
{
    public function run()
    {
        $superAdmin = Role::findByName('Super Administrateur');

        if ($superAdmin) {
            $superAdmin->givePermissionTo(Permission::all());
            echo "Toutes les permissions ont été attribuées au rôle.";
        } else {
            echo "Le rôle n'existe pas.";
        }
    }
}