<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('livraison_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_client_id')->constrained('facture_clients');
            $table->foreignId('depot_id')->constrained('depots');
            $table->string('numero', 20)->unique();
            $table->datetime('date_livraison');
            $table->datetime('date_validation')->nullable();
            $table->enum('statut', ['brouillon', 'valide', 'annule'])->default('brouillon');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->datetime('validated_at')->nullable();
            $table->foreignId('mouvement_stock_id')->nullable()->constrained('stock_mouvements');
            $table->timestamps();
            $table->softDeletes();

            // Index pour optimiser les recherches
            $table->index(['facture_client_id', 'statut']);
            $table->index(['depot_id', 'date_livraison']);
            $table->index('numero');
        });
    }

    public function down()
    {
        Schema::dropIfExists('livraison_clients');
    }
};
