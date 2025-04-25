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
        Schema::table('bon_commandes', function (Blueprint $table) {
            $table->bigInteger('cout_transport')->nullable()->default(0);
            $table->bigInteger('cout_chargement')->nullable()->default(0);
            $table->bigInteger('autre_cout')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bon_commandes', function (Blueprint $table) {
            //
        });
    }
};
