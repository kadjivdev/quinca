<?php

namespace Database\Seeders;

use App\Models\Securite\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
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

    public function run()
    {
        $permissionGroups = [
            'Administration' => array_merge(
                $this->createCrudValidatePermissions('utilisateurs', 'users', 'Administration'),
                $this->createCrudValidatePermissions('rôles', 'roles', 'Administration')
            ),

            'Paramètre' => array_merge(
                $this->createCrudValidatePermissions('configurations', 'configuration', 'Paramètre'),
                $this->createCrudValidatePermissions('points de vente', 'point-vente', 'Paramètre'),
                $this->createCrudValidatePermissions('magasins', 'depot', 'Paramètre'),
                $this->createCrudValidatePermissions('caisses', 'caisse', 'Paramètre'),
                $this->createCrudValidatePermissions('unités', 'unite-mesure', 'Paramètre'),
                $this->createCrudValidatePermissions('conversions', 'conversion', 'Paramètre'),
                $this->createCrudValidatePermissions('chauffeurs', 'chauffeur', 'Paramètre'),
                $this->createCrudValidatePermissions('véhicules', 'vehicule', 'Paramètre')
            ),

            'Catalogue' => array_merge(
                $this->createCrudValidatePermissions('familles d\'articles', 'famille-article', 'Catalogue'),
                $this->createCrudValidatePermissions('articles', 'articles', 'Catalogue'),
                $this->createCrudValidatePermissions('tarifications', 'tarification', 'Catalogue')
            ),

            'Achat' => array_merge(
                $this->createCrudValidatePermissions('fournisseurs', 'fournisseur', 'Achat'),
                $this->createCrudValidatePermissions('pré-commandes', 'programmations', 'Achat'),
                $this->createCrudValidatePermissions('bons de commande', 'bon-commandes', 'Achat'),
                $this->createCrudValidatePermissions('factures fournisseur', 'factures', 'Achat'),
                $this->createCrudValidatePermissions('règlements fournisseur', 'reglements', 'Achat'),
                $this->createCrudValidatePermissions('livraisons fournisseur', 'livraisons', 'Achat'),
                $this->createCrudValidatePermissions('approvisionnement fournisseur', 'approvisionnements', 'Achat'),
            ),

            'Ventes' => array_merge(
                $this->createCrudValidatePermissions('clients', 'vente.clients', 'Ventes'),
                $this->createCrudValidatePermissions('sessions de caisse', 'vente.sessions', 'Ventes'),
                $this->createCrudValidatePermissions('factures client', 'vente.facture', 'Ventes'),
                $this->createCrudValidatePermissions('règlements client', 'vente.reglement', 'Ventes'),
                $this->createCrudValidatePermissions('livraisons client', 'vente.livraisons', 'Ventes'),
                $this->createCrudValidatePermissions('factures proforma', 'facture.proformas', 'Ventes'),
                [
                    "Voir les détails" => "facture.proformas.details",
                ]
            ),

            'Revendeur' => array_merge(
                $this->createCrudValidatePermissions('factures revendeur', 'revendeur.facture', 'Revendeur'),
                $this->createCrudValidatePermissions('ventes spéciales', 'revendeur.speciales', 'Revendeur'),
                [
                    'Voir les validations vente' => 'revendeur.normale.rapport.view',
                    'Voir les validations spéciales' => 'revendeur.speciale.rapport.view'
                ]
            ),

            'Rapports Achats' => [
                'Voir rapports pré-commandes' => 'rapports.pre-commandes.view',
                'Voir rapports bon commandes' => 'rapports.bon-commandes.view',
                'Voir rapports factures achat' => 'rapports.facture-achats.view',
                'Voir rapports livraisons achat' => 'rapports.livraison-achats.view',
                'Voir rapports règlements achat' => 'rapports.reglement-achats.view',
                'Voir rapports compte fournisseur' => 'rapports.compte-fournisseur.view',
                'Exporter rapports achats' => 'rapports.achats.export'
            ],

            'Rapports Ventes' => [
                'Voir ventes par article' => 'rapports.ventes-articles.view',
                'Voir ventes par famille' => 'rapports.ventes-familles.view',
                'Voir ventes par client' => 'rapports.ventes-clients.view',
                'Voir ventes journalières' => 'rapports.vente-journaliere.view',
                'Voir suivi créances' => 'rapports.creances.view',
                'Voir rapports sessions' => 'vente.sessions.rapport.view',
                'Voir compte client' => 'rapports.compte-client.view',
                'Exporter rapports ventes' => 'rapports.ventes.export'
            ],

            'Rapports Stocks' => [
                'Voir mouvements stock' => 'rapports.mouvement-stock.view',
                'Voir stock disponible' => 'rapports.stock-dispo.view',
                'Voir rotations stock' => 'stock.rotation.view',
                'Exporter rapports stocks' => 'rapports.stocks.export'
            ],
        ];

        // Création des permissions
        $allPermissions = [];
        foreach ($permissionGroups as $group => $permissions) {
            foreach ($permissions as $description => $permission) {
                $createdPermission = Permission::firstOrCreate(
                    ['name' => $permission, 'guard_name' => 'web'],
                    ['name' => $permission, 'group_name' => $group, 'description' => $description]
                );
                $allPermissions[] = $createdPermission;
            }
        }

        // Création des rôles
        $roles = [
            'Super Administrateur',
            'Administrateur',
            'Manager',
            'Vendeur',
            'Comptable',
            'Magasinier',
            'Revendeur'
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Attribution de toutes les permissions au super-admin
        $superAdmin = Role::findByName('Super Administrateur');
        $superAdmin->syncPermissions($allPermissions);

        // Assigner le rôle de super administrateur à l'utilisateur avec l'ID 1
        $user = User::find(1);
        if ($user) {
            $user->assignRole('Super Administrateur');
        }
    }
}
