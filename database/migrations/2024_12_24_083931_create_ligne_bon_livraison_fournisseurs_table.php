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
        Schema::create('ligne_bon_livraison_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('livraison_id')->constrained('bon_livraison_fournisseurs');
            $table->foreignId('article_id')->constrained('articles');
            $table->foreignId('unite_mesure_id')->constrained('unite_mesures');
            $table->decimal('quantite', 10, 2);
            $table->decimal('quantite_supplementaire', 10, 2)->default(0);
            $table->text('commentaire')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // Index pour améliorer les performances
            $table->index('livraison_id');
            $table->index('article_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    
    public function down(): void
    {
        Schema::dropIfExists('ligne_bon_livraison_fournisseurs');
    }
};
