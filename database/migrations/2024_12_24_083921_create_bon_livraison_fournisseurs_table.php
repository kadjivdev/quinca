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
        Schema::create('bon_livraison_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->date('date_livraison');
            $table->foreignId('facture_id')->constrained('facture_fournisseurs');
            $table->foreignId('point_de_vente_id')->constrained('point_de_ventes');
            $table->foreignId('depot_id')->constrained('depots');
            $table->foreignId('fournisseur_id')->constrained('fournisseurs');
            $table->foreignId('vehicule_id')->nullable()->constrained('vehicules');
            $table->foreignId('chauffeur_id')->nullable()->constrained('chauffeurs');
            $table->text('commentaire')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index pour améliorer les performances
            $table->index('code');
            $table->index('date_livraison');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bon_livraison_fournisseurs');
    }
};
