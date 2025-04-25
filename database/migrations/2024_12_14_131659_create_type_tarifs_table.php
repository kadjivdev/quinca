<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('type_tarifs', function (Blueprint $table) {
            $table->id();
            $table->string('code_type_tarif')->unique();
            $table->string('libelle_type_tarif');
            $table->text('description')->nullable();
            $table->boolean('statut')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Insertion des types de tarif par défaut
        DB::table('type_tarifs')->insert([
            [
                'code_type_tarif' => 'STANDARD',
                'libelle_type_tarif' => 'Tarif Standard',
                'description' => 'Tarif de base standard pour tous les clients',
                'statut' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code_type_tarif' => 'PROMO',
                'libelle_type_tarif' => 'Tarif Promotionnel',
                'description' => 'Tarif pour les promotions et offres spéciales',
                'statut' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code_type_tarif' => 'VIP',
                'libelle_type_tarif' => 'Tarif VIP',
                'description' => 'Tarif spécial pour les clients VIP',
                'statut' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code_type_tarif' => 'SPECIAL',
                'libelle_type_tarif' => 'Tarif Spécial',
                'description' => 'Tarif pour les cas particuliers',
                'statut' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code_type_tarif' => 'GROSSISTE',
                'libelle_type_tarif' => 'Tarif Grossiste',
                'description' => 'Tarif pour les achats en gros',
                'statut' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code_type_tarif' => 'DETAILLANT',
                'libelle_type_tarif' => 'Tarif Détaillant',
                'description' => 'Tarif pour les détaillants',
                'statut' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('type_tarifs');
    }
};
