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
        Schema::table('ligne_bon_livraison_fournisseurs', function (Blueprint $table) {
            $table->foreignId('unite_supplementaire_id')->nullable()->after('quantite_supplementaire')->constrained('ligne_bon_livraison_fournisseurs');
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
