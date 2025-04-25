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
            'Ventes' => [
                // 'Associer Proforma' => 'associer.proforma',
                'Account Client' => 'accompte.client',
            ],
            
            'Revendeur' => array_merge(
                $this->createCrudValidatePermissions('clients dans panel revendeur', 'revendeur.clients', 'Ventes'),
            ),
        ];

        $permissions = [];

        foreach ($permissions_groups as $group => $permissions) {
            foreach ($permissions as $description => $permission) {
                $createdPermission = Permission::firstOrCreate(
                    ['name' => $permission, 'guard_name' => 'web'],
                    ['name' => $permission, 'group_name' => $group, 'description' => $description]
                );

                $permissions[] = $createdPermission;
            }
        }

        // Attribution de toutes les permissions au super-admin
        $superAdmin = Role::findByName('Super Administrateur');
        $superAdmin->syncPermissions($permissions);
    }
}
