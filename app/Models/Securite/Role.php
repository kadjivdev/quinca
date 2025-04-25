<?php

namespace App\Models\Securite;


use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
    ];

    public static function defaultRoles()
    {
        return [
            'super-admin' => [
                'name' => 'super-admin',
                'permissions' => ['*']  // Tous les droits
            ],
            'gestionnaire-ticket' => [
                'name' => 'gestionnaire-ticket',
                'permissions' => [
                    'generate_tickets',
                    'view_tickets',
                    'view_validation_stats',
                    'view_lot_statistics',
                    'view_zones',
                    'view_vehicules',
                    'view_chauffeurs',
                ]
            ],
            'validateur-ticket' => [
                'name' => 'validateur-ticket',
                'permissions' => [
                    'validate_tickets',
                    'view_tickets',
                    'view_validation_stats'
                ]
            ]
        ];
    }
}
