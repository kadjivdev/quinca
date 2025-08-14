<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //suppression de la contrainte
        Schema::table('ligne_bon_livraison_fournisseurs', function (Blueprint $table) {
            $table->dropForeign(['unite_supplementaire_id']);
        });

        //recreation 
        Schema::table('ligne_bon_livraison_fournisseurs', function (Blueprint $table) {
            $table->foreign('unite_supplementaire_id')
                ->references('id')
                ->on('unite_mesures') // nom correct ici
                ->nullOnDelete(); // ou cascadeOnDelete() selon ton besoin
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ligne_bon_livraison_fournisseurs', function (Blueprint $table) {
            //
        });
    }
};
