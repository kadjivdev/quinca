<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('conversion_unites', function (Blueprint $table) {
            // Supprimer l'ancienne contrainte d'unicité
            if (DB::getSchemaBuilder()->hasIndex('conversion_unites', 'conversion_unique')) {
                $table->dropUnique('conversion_unique');
            }

            // Ajouter la nouvelle contrainte d'unicité incluant article_id
            $table->unique(['unite_source_id', 'unite_dest_id', 'article_id'], 'conversion_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversion_unites', function (Blueprint $table) {
            // Supprimer la nouvelle contrainte
            $table->dropUnique('conversion_unique');

            // Restaurer l'ancienne contrainte
            $table->unique(['unite_source_id', 'unite_dest_id'], 'conversion_unique');
        });
    }
};
