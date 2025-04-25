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
        Schema::create('reglement_clients', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();

            // Clés étrangères
            $table->foreignId('session_caisse_id')
                ->nullable()
                ->constrained('session_caisses')
                ->nullOnDelete();

            $table->foreignId('facture_client_id')
                ->constrained('facture_clients')
                ->onDelete('restrict');

            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict');

            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Informations principales
            $table->dateTime('date_reglement');
            $table->decimal('montant', 12, 3);
            $table->enum('type_reglement', [
                'espece',
                'cheque',
                'virement',
                'carte_bancaire',
                'MoMo',
                'Flooz',
                'Celtis_Pay',
                'Effet',
                'Avoir'
            ]);
            $table->enum('statut', ['brouillon', 'validee', 'annulee'])
                ->default('brouillon');

            // Informations complémentaires
            $table->string('reference_preuve')->nullable();
            $table->string('banque')->nullable();
            $table->date('date_echeance')->nullable();
            $table->text('notes')->nullable();

            // Validation
            $table->dateTime('validated_at')->nullable();

            // Timestamps et soft delete
            $table->timestamps();
            $table->softDeletes();

            // Index pour optimisation
            $table->index('numero');
            $table->index('date_reglement');
            $table->index('type_reglement');
            $table->index('statut');
            $table->index(['facture_client_id', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reglement_clients');
    }
};
