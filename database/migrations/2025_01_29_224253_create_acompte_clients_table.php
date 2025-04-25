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
        Schema::create('acompte_clients', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('reference')->unique();
            $table->enum('type_paiement', ['espece', 'virement', 'cheque']);
            $table->decimal('montant', 15, 3);
            $table->foreignId('client_id')
                ->constrained('clients')
                ->onDelete('restrict');
            $table->text('observation')->nullable();
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])
                ->default('en_attente');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('point_de_vente_id')
                ->constrained('point_de_ventes')
                ->onDelete('restrict');
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('date');
            $table->index('type_paiement');
            $table->index('client_id');
            $table->index('statut');
            $table->index('validated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acompte_clients');
    }
};
