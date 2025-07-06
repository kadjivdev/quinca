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
            $table->foreignId("inventaire_id")
                ->nullable()
                ->constrained("inventaires", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facture_revendeurs', function (Blueprint $table) {
            $table->dropForeign(["inventaire_id"]);
            $table->removeColumn("inventaire_id");
        });
    }
};
