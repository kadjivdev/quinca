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
        Schema::create('unite_mesures', function (Blueprint $table) {
            $table->id();

            // Informations principales
            $table->string('code_unite', 10)->unique()
                  ->comment('Code unique de l\'unité de mesure (ex: KG, M, L)');

            $table->string('libelle_unite', 50)
                  ->comment('Libellé de l\'unité de mesure (ex: Kilogramme, Mètre, Litre)');

            $table->text('description')->nullable()
                  ->comment('Description optionnelle de l\'unité de mesure');

            // Flags
            $table->boolean('unite_base')->default(false)
                  ->comment('Indique si c\'est une unité de base');

            $table->boolean('statut')->default(true)
                  ->comment('Statut de l\'unité (actif/inactif)');

            // Timestamps standard Laravel
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('statut');
            $table->index('unite_base');
        });

        // Schema::create('conversion_unites', function (Blueprint $table) {
        //     $table->id();

        //     // Clés étrangères
        //     $table->foreignId('unite_source_id')
        //           ->constrained('unite_mesures')
        //           ->onDelete('restrict')
        //           ->onUpdate('restrict')
        //           ->comment('ID de l\'unité source');

        //     $table->foreignId('unite_dest_id')
        //           ->constrained('unite_mesures')
        //           ->onDelete('restrict')
        //           ->onUpdate('restrict')
        //           ->comment('ID de l\'unité de destination');

        //     $table->foreignId('famille_id')
        //           ->nullable()
        //           ->constrained('famille_articles')
        //           ->onDelete('restrict')
        //           ->onUpdate('restrict')
        //           ->comment('ID de la famille d\'articles (optionnel)');

        //     // Coefficient de conversion
        //     $table->decimal('coefficient', 20, 10)
        //           ->comment('Coefficient de conversion entre les unités');

        //     $table->boolean('statut')->default(true)
        //           ->comment('Statut de la conversion (actif/inactif)');

        //     // Timestamps standard Laravel
        //     $table->timestamps();
        //     $table->softDeletes();

        //     // Index et contraintes
        //     $table->index('statut');
        //     $table->unique(['unite_source_id', 'unite_dest_id', 'famille_id'], 'unique_conversion');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('conversion_unites');
        Schema::dropIfExists('unite_mesures');
    }
};
