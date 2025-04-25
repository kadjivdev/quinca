<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('code_article', 50)->unique()->comment('Code unique de l\'article');
            $table->string('designation', 191)->comment('Nom/Désignation de l\'article');  // Limité à 191
            $table->text('description')->nullable()->comment('Description détaillée de l\'article');

            $table->foreignId('famille_id')
                  ->constrained('famille_articles')
                  ->onDelete('restrict')
                  ->comment('Référence à la famille d\'articles');

            $table->decimal('stock_minimum', 10, 2)->default(0)->comment('Niveau minimum de stock');
            $table->decimal('stock_maximum', 10, 2)->default(0)->comment('Niveau maximum de stock');
            $table->decimal('stock_securite', 10, 2)->default(0)->comment('Niveau de sécurité du stock');
            $table->decimal('stock_actuel', 10, 2)->default(0)->comment('Niveau actuel du stock');

            $table->string('code_barre', 50)->nullable()->unique()->comment('Code-barres de l\'article');
            $table->boolean('stockable')->default(true)->comment('Indique si l\'article est stockable');
            $table->string('emplacement_stock', 100)->nullable()->comment('Emplacement physique du stock');

            $table->string('statut')->default('actif')->comment('Statut de l\'article (actif/inactif)');
            $table->string('photo')->nullable()->comment('Chemin de la photo de l\'article');

            $table->timestamps();
            $table->softDeletes();

            // Index séparés au lieu d'un index combiné
            $table->index('designation');
            $table->index('code_article');
            $table->index('code_barre');
            $table->index('stockable');
            $table->index('statut');
        });

        DB::statement("ALTER TABLE `articles` comment 'Table de gestion des articles et leurs stocks'");
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
