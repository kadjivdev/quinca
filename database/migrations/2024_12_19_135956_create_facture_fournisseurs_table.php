<?php
// {timestamp}_create_facture_fournisseurs_table.php

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
        Schema::create('facture_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->date('date_facture');
            $table->foreignId('bon_commande_id')->constrained('bon_commandes');
            $table->foreignId('point_de_vente_id')->constrained('point_de_ventes');
            $table->foreignId('fournisseur_id')->constrained('fournisseurs');
            $table->decimal('montant_ht', 15, 2)->default(0);
            $table->decimal('montant_tva', 15, 2)->default(0);
            $table->decimal('montant_aib', 15, 2)->default(0);
            $table->decimal('montant_ttc', 15, 2)->default(0);
            $table->enum('statut_livraison', [
                'NON_LIVRE',
                'PARTIELLEMENT_LIVRE',
                'LIVRE'
            ])->default('NON_LIVRE');
            $table->enum('statut_paiement', [
                'NON_PAYE',
                'PARTIELLEMENT_PAYE',
                'PAYE'
            ])->default('NON_PAYE');
            $table->text('commentaire')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('code');
            $table->index('date_facture');
            $table->index(['bon_commande_id', 'fournisseur_id']);
            $table->index('statut_livraison');
            $table->index('statut_paiement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facture_fournisseurs');
    }
};
