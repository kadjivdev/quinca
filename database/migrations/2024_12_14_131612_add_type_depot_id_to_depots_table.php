<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('depots', function (Blueprint $table) {
            // Ajout de la colonne type_depot_id après la colonne actif
            $table->foreignId('type_depot_id')
                  ->nullable()
                  ->after('actif')
                  ->constrained('type_depots')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('depots', function (Blueprint $table) {
            // Suppression de la contrainte de clé étrangère
            $table->dropForeign(['type_depot_id']);
            // Suppression de la colonne
            $table->dropColumn('type_depot_id');
        });
    }
};
