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
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('departement_id')->nullable()->after('point_de_vente_id')->constrained('departements');
            $table->foreignId('agent_id')->nullable()->after('departement_id')->constrained('agents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['departement_id']);
            $table->dropForeign(['agent_id']);
            $table->dropColumn('departement_id');
            $table->dropColumn('agent_id');
        });
    }
};
