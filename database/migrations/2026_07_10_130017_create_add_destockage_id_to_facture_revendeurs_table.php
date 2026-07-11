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
            $table->foreignId("destockage_id")
                ->after("client_id")
                ->comment("Le destockage concerné")
                ->nullable()
                ->constrained("destockages", "id")
                ->onUpdate("CASCADE")
                ->onDelete("set null");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facture_revendeurs', function (Blueprint $table) {
            $table->dropForeign(["destockage_id"]);
            $table->dropColumn("destockage_id");
        });
    }
};
