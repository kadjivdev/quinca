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
        Schema::table('stock_mouvements', function (Blueprint $table) {
            $table->foreignId('inventaire_id')
                ->nullable()
                ->after("unite_mesure_id")
                ->comment("L'inventaire concerné")
                ->constrained("inventaires")
                ->onUpdate('CASCADE')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_mouvements', function (Blueprint $table) {
            $table->dropForeign(["inventaire_id"]);
            $table->dropColumn("inventaire_id");
        });
    }
};
