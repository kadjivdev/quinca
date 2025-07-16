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
        Schema::create('compte_clients', function (Blueprint $table) {
            $table->id();
            $table->date('date_op');
            $table->double('montant_op');

            $table->enum("type_op", ["FAC_CLT", "FAC_REV", "REG_CLT", "REG_REV", "AC_CLT"])
                ->description("Le type de l'opération");

            $table->foreignId('client_id')
                ->nullable()
                ->constrained("clients", "id")
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE')
                ->description("Le Client concerné");

            $table->foreignId('accompte_client')
                ->nullable()
                ->constrained("acompte_clients", "id")
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE')
                ->description("Opération des accomptes clients");

            $table->foreignId('facture_client_id')
                ->nullable()
                ->constrained("facture_clients", "id")
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE')
                ->description("Opération des factures clients");

            $table->foreignId('facture_revendeur_id')
                ->nullable()
                ->constrained("facture_revendeurs", "id")
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE')
                ->description("Opération des factures revendeurs");

            $table->foreignId('reglement_clt')
                ->nullable()
                ->constrained("reglement_clients", "id")
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE')
                ->description("Opération des reglements clients");

            $table->foreignId('reglement_rev')
                ->nullable()
                ->constrained("reglement_revendeurs", "id")
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE')
                ->description("Opération des reglements revendeurs");

            $table->foreignId('user_id')
                ->nullable()
                ->constrained("users", "id")
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compte_clients');
    }
};
