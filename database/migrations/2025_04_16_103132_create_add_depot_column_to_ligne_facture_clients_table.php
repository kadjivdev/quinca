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
        Schema::table('ligne_facture_clients', function (Blueprint $table) {
            $table->foreignId("depot")
                ->nullable()
                ->constrained("depots", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::table('ligne_facture_clients', function (Blueprint $table) {
            $table->dropForeign("depot");
            $table->dropColumn("depot");
        });
    }
};
