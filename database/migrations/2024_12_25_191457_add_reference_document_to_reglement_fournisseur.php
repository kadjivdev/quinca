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
            $table->string('reference_document')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reglement_fournisseurs', function (Blueprint $table) {
            $table->dropColumn('reference_document');
        });
    }
};
