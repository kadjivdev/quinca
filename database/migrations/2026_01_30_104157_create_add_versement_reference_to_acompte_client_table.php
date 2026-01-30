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
        Schema::table('acompte_clients', function (Blueprint $table) {
            $table->text("versement_reference")->nullable()->after("reference")->comment("Reference du versement");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acompte_clients', function (Blueprint $table) {
           $table->dropColumn("versement_reference");
        });
    }
};
