<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversion_unites', function (Blueprint $table) {
            $table->id();

            // Clés étrangères
            $table->foreignId('unite_source_id')
                  ->constrained('unite_mesures')
                  ->onDelete('restrict')
                  ->onUpdate('restrict')
                  ->comment('ID de l\'unité source');

            $table->foreignId('unite_dest_id')
                  ->constrained('unite_mesures')
                  ->onDelete('restrict')
                  ->onUpdate('restrict')
                  ->comment('ID de l\'unité de destination');

            $table->foreignId('famille_id')
                  ->nullable()
                  ->constrained('famille_articles')
                  ->onDelete('restrict')
                  ->onUpdate('restrict')
                  ->comment('ID de la famille d\'articles (optionnel, pour des conversions spécifiques à une famille)');

            // Coefficient de conversion
            $table->decimal('coefficient', 20, 10)
                  ->comment('Coefficient multiplicateur pour la conversion');

            // Statut
            $table->boolean('statut')
                  ->default(true)
                  ->comment('État de la conversion (active/inactive)');

            // Timestamps et soft delete
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('statut');
            $table->index(['unite_source_id', 'unite_dest_id']);
            $table->index('famille_id');

            // Index composites pour les recherches bidirectionnelles
            $table->index(['unite_source_id', 'unite_dest_id', 'deleted_at'], 'conversion_unites_forward_idx');
            $table->index(['unite_dest_id', 'unite_source_id', 'deleted_at'], 'conversion_unites_reverse_idx');

            // Contraintes d'unicité
            $table->unique(
                ['unite_source_id', 'unite_dest_id', 'famille_id'],
                'conversion_unique'
            );
        });

        // Ajouter une contrainte pour le coefficient positif via TRIGGER
        DB::unprepared('
            CREATE TRIGGER check_positive_coefficient
            BEFORE INSERT ON conversion_unites
            FOR EACH ROW
            BEGIN
                IF NEW.coefficient <= 0 THEN
                    SIGNAL SQLSTATE \'45000\'
                    SET MESSAGE_TEXT = \'Le coefficient doit être positif\';
                END IF;

            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer le trigger
        DB::unprepared('DROP TRIGGER IF EXISTS check_positive_coefficient');

        Schema::dropIfExists('conversion_unites');
    }
};
