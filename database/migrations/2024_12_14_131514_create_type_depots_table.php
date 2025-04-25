<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('type_depots', function (Blueprint $table) {
            $table->id();
            $table->string('code_type_depot')->unique();
            $table->string('libelle_type_depot');
            $table->text('description')->nullable();
            $table->boolean('statut')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Insérer les types de magasin par défaut
        DB::table('type_depots')->insert([
            [
                'code_type_depot' => 'PRINCIPAL',
                'libelle_type_depot' => 'Magasin Principal/Central',
                'description' => 'Magasin principal pour le stockage central',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code_type_depot' => 'TRANSIT',
                'libelle_type_depot' => 'Magasin de Transit/Logistique',
                'description' => 'Magasin pour le transit et la logistique',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code_type_depot' => 'POINT_VENTE',
                'libelle_type_depot' => 'Magasin Point de Vente',
                'description' => 'Magasin pour les points de vente',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code_type_depot' => 'STOCKAGE',
                'libelle_type_depot' => 'Magasin de Stockage',
                'description' => 'Magasin pour le stockage général',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('type_depots');
    }
};
