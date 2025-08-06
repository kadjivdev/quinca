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
        Schema::create('avances', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('reference')->unique();
            $table->enum('type_paiement', ['espece', 'virement', 'cheque']);
            $table->decimal('montant', 15, 3);
            $table->foreignId('fournisseur_id')
                ->nullable()
                ->constrained('fournisseurs', "id")
                ->onDelete('restrict');
            $table->foreignId('requete_id')
                ->nullable()
                ->constrained('requete_fournisseurs', "id")
                ->onDelete('restrict');
            $table->text('observation')->nullable();
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])
                ->default('en_attente');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users', "id")
                ->nullOnDelete();
            $table->foreignId('point_de_vente_id')
                ->nullable()
                ->constrained('point_de_ventes', "id")
                ->onDelete('restrict');
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('users', "id")
                ->nullOnDelete();
            $table->timestamps();
            $table->foreignId("deleted_by")
                ->nullable()
                ->constrained("users", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
            $table->softDeletes();

            // Index
            $table->index('date');
            $table->index('type_paiement');
            $table->index('fournisseur_id');
            $table->index('statut');
            $table->index('validated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avances');
    }
};
