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
        Schema::create('tarifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('article_id')
                  ->constrained('articles')
                  ->onDelete('restrict')
                  ->onUpdate('restrict')
                  ->comment('ID de l\'article concerné');

            $table->foreignId('type_tarif_id')
                  ->constrained('type_tarifs')
                  ->onDelete('restrict')
                  ->onUpdate('restrict')
                  ->comment('ID du type de tarif');

            $table->decimal('prix', 10, 2)
                  ->comment('Prix de l\'article pour ce type de tarif');

            $table->boolean('statut')
                  ->default(true)
                  ->comment('État du tarif (actif/inactif)');

            // Timestamps et soft delete
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('statut');

            // Contrainte d'unicité
            $table->unique(['article_id', 'type_tarif_id'], 'unique_article_type_tarif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifications');
    }
};
