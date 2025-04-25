<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ligne_facture_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_client_id')->constrained('facture_clients')->onDelete('cascade');
            $table->foreignId('article_id')->constrained('articles');
            $table->foreignId('unite_vente_id')->constrained('unite_mesures');
            // $table->foreignId('tarification_id')->constrained('tarifications');

            // Quantités
            $table->decimal('quantite', 15, 3);          // Quantité dans l'unité de vente
            $table->decimal('quantite_base', 15, 3);     // Quantité convertie en unité de base
            $table->decimal('quantite_livree', 15, 3)->default(0); // Quantité déjà livrée en unité de base

            // Prix et montants
            $table->decimal('prix_unitaire_ht', 15, 3);
            $table->decimal('taux_remise', 5, 2)->default(0);
            $table->decimal('montant_remise', 15, 3)->default(0);
            $table->decimal('montant_ht', 15, 3);
            $table->decimal('montant_ht_apres_remise', 15, 3);
            $table->decimal('taux_tva', 5, 2)->default(0);
            $table->decimal('montant_tva', 15, 3)->default(0);
            $table->decimal('taux_aib', 5, 2)->default(0);
            $table->decimal('montant_aib', 15, 3)->default(0);
            $table->decimal('montant_ttc', 15, 3);

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ligne_facture_clients');
    }
};
