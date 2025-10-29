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
        Schema::table('reversements', function (Blueprint $table) {
            $table->decimal('recette', 20, 2)->default(0)->change();
            $table->decimal('depense', 20, 2)->default(0)->change();
            $table->decimal('recette_to_reverse', 20, 2)->default(0)->change();
            $table->decimal('montant_reversed', 20, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reversements', function (Blueprint $table) {
            //
        });
    }
};
