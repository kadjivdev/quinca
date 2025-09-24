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
        Schema::table('tarifications', function (Blueprint $table) {
            // 1. Supprimer la foreign key si elle existe
            $table->dropForeign(['article_id']); // adapte si besoin
            $table->dropForeign(['type_tarif_id']); // adapte si besoin

            // Supprimer la contrainte d'unicité
            $table->dropUnique('unique_article_type_tarif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarifications', function (Blueprint $table) {
            // 1. Restaurer l’unicité
            $table->unique(['article_id', 'type_tarif_id'], 'unique_article_type_tarif');

            // 2. Restaurer les foreign keys
            $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
            $table->foreign('type_tarif_id')->references('id')->on('types_tarif')->onDelete('cascade');
        });
    }
};
