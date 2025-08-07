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
        Schema::table('ligne_bon_commandes', function (Blueprint $table) {
            $table->decimal('quantite_base', 15, 3)
                ->after("quantite")
                ->nullable();
            $table->foreignId('unite_mesure_base_id')
                ->after("unite_mesure_id")
                ->nullable()
                ->constrained('unite_mesures')
                ->onUpdate("CASCADE")
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ligne_bon_commandes', function (Blueprint $table) {
            $table->dropForeign(["unite_mesure_base_id"]);
            $table->dropColumn(["quantite_base", "unite_mesure_base_id"]);
        });
    }
};
