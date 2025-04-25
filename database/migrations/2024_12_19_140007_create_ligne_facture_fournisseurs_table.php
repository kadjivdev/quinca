<?php

// {timestamp}_create_ligne_facture_fournisseurs_table.php

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
        Schema::create('ligne_facture_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('facture_fournisseurs');
            $table->foreignId('article_id')->constrained('articles');
            $table->foreignId('unite_mesure_id')->constrained('unite_mesures');
            $table->decimal('quantite', 15, 3);
            $table->decimal('prix_unitaire', 15, 2);
            $table->decimal('taux_tva', 5, 2)->default(0);
            $table->decimal('taux_aib', 5, 2)->default(0);
            $table->decimal('montant_ht', 15, 2)->default(0);
            $table->decimal('montant_tva', 15, 2)->default(0);
            $table->decimal('montant_aib', 15, 2)->default(0);
            $table->decimal('montant_ttc', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index(['facture_id', 'article_id']);
            $table->index('unite_mesure_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ligne_facture_fournisseurs');
    }
};
