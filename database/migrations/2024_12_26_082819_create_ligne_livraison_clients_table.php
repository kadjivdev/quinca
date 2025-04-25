<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ligne_livraison_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('livraison_client_id')->constrained('livraison_clients');
            $table->foreignId('ligne_facture_id')->constrained('ligne_facture_clients');
            $table->foreignId('article_id')->constrained('articles');
            $table->foreignId('unite_vente_id')->constrained('unite_mesures');
            $table->decimal('quantite', 12, 3);
            $table->decimal('quantite_base', 12, 3);
            $table->decimal('prix_unitaire', 12, 3);
            $table->decimal('montant_total', 12, 3);
            $table->foreignId('mouvement_stock_id')->nullable()->constrained('stock_mouvements');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index pour optimiser les recherches
            $table->index(['livraison_client_id', 'article_id']);
            $table->index(['ligne_facture_id', 'article_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ligne_livraison_clients');
    }
};
