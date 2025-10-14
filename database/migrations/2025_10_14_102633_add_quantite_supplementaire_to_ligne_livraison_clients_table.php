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
        Schema::table('ligne_livraison_clients', function (Blueprint $table) {
            $table->decimal('quantite_supplementaire', 12, 3)->after("quantite")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ligne_livraison_clients', function (Blueprint $table) {
            //
        });
    }
};
