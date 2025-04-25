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
        Schema::create('facture_revendeurs', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('client_id')->constrained('clients');
            $table->date('date_facture');

            // Montants
            $table->decimal('montant_ht', 15, 3)->default(0);
            $table->decimal('taux_remise', 5, 2)->default(0);
            $table->decimal('montant_remise', 15, 3)->default(0);
            $table->decimal('montant_ht_apres_remise', 15, 3)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(0);
            $table->decimal('montant_tva', 15, 3)->default(0);
            $table->decimal('taux_aib', 5, 2)->default(0);
            $table->decimal('montant_aib', 15, 3)->default(0);
            $table->decimal('montant_ttc', 15, 3)->default(0);
            $table->decimal('montant_regle', 15, 3)->default(0);

            // États et dates
            $table->enum('statut', ['brouillon', 'validee', 'annulee', 'partiellement_payee', 'payee'])->default('brouillon');
            $table->timestamp('date_validation')->nullable();
            $table->text('notes')->nullable();

            // Traçabilité
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('validated_by')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facture_revendeurs');
    }
};
