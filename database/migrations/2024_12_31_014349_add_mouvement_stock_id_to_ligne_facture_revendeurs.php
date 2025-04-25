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
        Schema::table('ligne_facture_revendeurs', function (Blueprint $table) {
            $table->foreignId('mouvement_stock_id')->nullable()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ligne_facture_revendeurs', function (Blueprint $table) {
            $table->dropForeign('mouvement_stock_id');
            $table->dropColumn('mouvement_stock_id');
        });
    }
};
