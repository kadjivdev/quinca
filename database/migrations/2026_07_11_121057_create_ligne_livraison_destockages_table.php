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
        Schema::create('ligne_livraison_destockages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('livraison_destockage_id')
                ->nullable()
                ->constrained('livraison_destockages')
                ->onUpdate("CASCADE")
                ->onDelete("set null");
            $table->foreignId('ligne_facture_id')
                ->nullable()
                ->constrained('ligne_facture_revendeurs')
                ->onUpdate("CASCADE")
                ->onDelete("set null");
            $table->foreignId('article_id')
                ->nullable()
                ->constrained('articles')
                ->onUpdate("CASCADE")
                ->onDelete("set null");
            $table->foreignId('unite_vente_id')
                ->nullable()
                ->constrained('unite_mesures')
                ->onUpdate("CASCADE")
                ->onDelete("set null");
            $table->foreignId('mouvement_stock_id')
                ->nullable()
                ->constrained('stock_mouvements')
                ->onUpdate("CASCADE")
                ->onDelete("set null");

            $table->decimal('quantite', 12, 3);
            $table->decimal('quantite_base', 12, 3);
            $table->decimal('quantite_supplementaire', 12, 3);
            $table->decimal('prix_unitaire', 12, 3);
            $table->decimal('montant_total', 12, 3);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ligne_livraison_destockages');
    }
};
