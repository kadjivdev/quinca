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
        Schema::table('requete_fournisseurs', function (Blueprint $table) {
            $table->foreignId('facture_id')
                ->after("fournisseur_id")
                ->comment("Facture founisseur achat")
                ->nullable()
                ->constrained("facture_fournisseurs", "id")
                ->onUpdate('cascade')
                ->onDelete('cascade')
            ;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requete_fournisseurs', function (Blueprint $table) {
            $table->dropForeign(["facture_id"]);
            $table->dropColumn("facture_id");
        });
    }
};
