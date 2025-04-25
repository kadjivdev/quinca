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
            $table->enum('type_vente', ['normale', 'speciale'])
                  ->nullable()
                  ->default('normale')
                  ->comment('Type de la vente pour ce qui concerne le nord')
                  ->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facture_revendeurs', function (Blueprint $table) {
            $table->dropColumn('type_vente');
        });
    }
};
