// create_ligne_bon_commandes_table.php
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
        Schema::create('ligne_bon_commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_commande_id')
                  ->constrained('bon_commandes')
                  ->onDelete('cascade');
            $table->foreignId('article_id')
                  ->constrained('articles')
                  ->onDelete('restrict');
            $table->foreignId('unite_mesure_id')
                  ->constrained('unite_mesures')
                  ->onDelete('restrict');
            $table->decimal('quantite', 15, 3)
                  ->default(0);
            $table->decimal('prix_unitaire', 15, 2)
                  ->default(0);
            $table->decimal('taux_remise', 5, 2)
                  ->default(0);
            $table->decimal('montant_ligne', 15, 2)
                  ->default(0);
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Index pour améliorer les performances
            $table->index('bon_commande_id');
            $table->index('article_id');
            $table->index('unite_mesure_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ligne_bon_commandes');
    }
};
