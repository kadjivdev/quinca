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
            $table->foreignId("fournisseur_id")
                ->nullable()
                ->constrained("fournisseurs", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reglement_fournisseurs', function (Blueprint $table) {
            $table->dropForeign("fournisseur_id");
            $table->dropColumn("fournisseur_id");
        });
    }
};
