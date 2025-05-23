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
        Schema::table('reglement_fournisseurs', function (Blueprint $table) {
            $table->foreignId('facture_fournisseur_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reglement_fournisseurs', function (Blueprint $table) {
            $table->dropForeign("facture_fournisseur_id");
            $table->dropColumn("facture_fournisseur_id");
        });
    }
};
