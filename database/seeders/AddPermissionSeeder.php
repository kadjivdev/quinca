<?php

namespace Database\Seeders;

use App\Models\Securite\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AddPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    private function createCrudValidatePermissions($name, $permission, $group)
    {
        return [
            "Voir les $name" => "$permission.view",
            "Créer des $name" => "$permission.create",
            "Modifier les $name" => "$permission.edit",
            "Supprimer des $name" => "$permission.delete",
            "Valider les $name" => "$permission.validate",
        ];
    }

    public function run(): void
    {
        $permissions_groups = [
            'Requêtes' => array_merge(
                $this->createCrudValidatePermissions('requetes', 'requetes', 'Ventes'),
            ),
            
            'Transports' => array_merge(
                $this->createCrudValidatePermissions('transports', 'transports', 'Ventes'),
            ),
        ];

        foreach ($permissions_groups as $group => $permissions) {
            foreach ($permissions as $description => $permission) {
                Permission::firstOrCreate(
                    ['name' => $permission, 'guard_name' => 'web'],
                    ['name' => $permission, 'group_name' => $group, 'description' => $description]
                );

            }
        }

        // Attribution de toutes les permissions au super-admin
        $superAdmin = Role::findByName('Super Administrateur');
        $superAdmin->syncPermissions(Permission::all());
    }
}
