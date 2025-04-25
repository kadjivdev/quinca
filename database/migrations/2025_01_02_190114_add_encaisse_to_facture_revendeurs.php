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
        Schema::table('facture_revendeurs', function (Blueprint $table) {
            $table->enum('encaisse', ['oui', 'non'])
                ->nullable()
                ->default('non')
                ->comment('Statut de la vente. Utile pour la validation des ventes du Nord')
                ->after('type_vente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facture_revendeurs', function (Blueprint $table) {
            $table->dropColumn('encaisse');
        });
    }
};
