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
            // Vérifier et supprimer l'ancienne contrainte d'unicité si elle existe
            if (DB::getSchemaBuilder()->hasIndex('conversion_unites', 'conversion_unique')) {
                $table->dropUnique('conversion_unique');
            }
            // Supprimer d'abord l'ancienne clé étrangère s'il elle existe
            if (Schema::hasColumn('conversion_unites', 'famille_id')) {
                $table->dropForeign(['famille_id']);
                $table->dropColumn('famille_id');
            }

            // Ajouter la nouvelle colonne article_id avec sa clé étrangère
            $table->unsignedBigInteger('article_id')->nullable()->after('unite_dest_id');
            $table->foreign('article_id')
                  ->references('id')
                  ->on('articles')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversion_unites', function (Blueprint $table) {
            // Supprimer la nouvelle clé étrangère et colonne
            $table->dropForeign(['article_id']);
            $table->dropColumn('article_id');

            // Restaurer l'ancienne colonne famille_id
            $table->unsignedBigInteger('famille_id')->nullable()->after('unite_dest_id');
            $table->foreign('famille_id')
                  ->references('id')
                  ->on('famille_articles')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }
};
