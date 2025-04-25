<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionCaissesTable extends Migration
{
    public function up(): void
    {
        // Table des sessions de caisse
        Schema::create('session_caisses', function (Blueprint $table) {
            $table->id();

            // Clés étrangères
            $table->foreignId('utilisateur_id')->constrained('users');
            $table->foreignId('caisse_id')->constrained('caisses');

            // Informations d'ouverture
            $table->dateTime('date_ouverture');
            $table->decimal('montant_ouverture', 15, 2);
            $table->text('observations')->nullable();

            // Informations de fermeture
            $table->dateTime('date_fermeture')->nullable();
            $table->decimal('montant_fermeture', 15, 2)->nullable();
            $table->text('observations_fermeture')->nullable();

            // Totaux calculés
            $table->decimal('total_encaissements', 15, 2)->default(0);
            $table->decimal('total_decaissements', 15, 2)->default(0);
            $table->decimal('solde_theorique', 15, 2)->default(0);
            $table->decimal('ecart', 15, 2)->default(0);

            // Statut de la session (ouverte/fermee)
            $table->enum('statut', ['ouverte', 'fermee'])->default('ouverte');

            // Timestamps et soft delete
            $table->timestamps();
            $table->softDeletes();

            // Index pour les recherches fréquentes
            $table->index('date_ouverture');
            $table->index('statut');
            $table->index(['caisse_id', 'statut']);
            $table->index(['utilisateur_id', 'statut']);
        });

        // Table des détails du comptage
        Schema::create('detail_comptages', function (Blueprint $table) {
            $table->id();

            // Clé étrangère vers la session
            $table->foreignId('session_caisse_id')
                  ->constrained('session_caisses')
                  ->onDelete('cascade');

            // Type de comptage (ouverture/fermeture)
            $table->enum('type_comptage', ['ouverture', 'fermeture']);

            // Informations du billet/pièce
            $table->integer('valeur');  // Valeur du billet/pièce (ex: 5000, 2000, etc.)
            $table->integer('quantite'); // Nombre de billets/pièces
            $table->decimal('montant', 15, 2); // Montant total (valeur × quantite)

            // Timestamps
            $table->timestamps();

            // Index pour les recherches fréquentes
            $table->index(['session_caisse_id', 'type_comptage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // On supprime d'abord la table qui a la clé étrangère
        Schema::dropIfExists('detail_comptages');
        Schema::dropIfExists('session_caisses');
    }

}

