// create_bon_commandes_table.php
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
        Schema::create('bon_commandes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->date('date_commande');
            $table->foreignId('programmation_id')
                  ->constrained('programmation_achats')
                  ->onDelete('restrict');
            $table->foreignId('point_de_vente_id')
                  ->constrained('point_de_ventes')
                  ->onDelete('restrict');
            $table->foreignId('fournisseur_id')
                  ->constrained('fournisseurs')
                  ->onDelete('restrict');
            $table->decimal('montant_total', 15, 2)
                  ->default(0);
            $table->text('commentaire')
                  ->nullable();
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
            $table->index('code');
            $table->index('date_commande');
            $table->index('programmation_id');
            $table->index('point_de_vente_id');
            $table->index('fournisseur_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bon_commandes');
    }
};
