<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('code_client', 20)->unique();
            $table->string('raison_sociale', 191);  // Limité à 191
            $table->string('ifu', 100)->nullable();  // Limité à 100
            $table->string('rccm', 100)->nullable(); // Limité à 100
            $table->string('telephone', 20)->nullable();
            $table->string('email', 191)->nullable(); // Limité à 191
            $table->string('adresse', 191)->nullable(); // Limité à 191
            $table->string('ville', 100)->nullable(); // Limité à 100

            // Informations commerciales
            $table->decimal('plafond_credit', 15, 3)->default(0);
            $table->integer('delai_paiement')->default(0);

            // État du compte
            $table->decimal('solde_initial', 15, 3)->default(0);
            $table->decimal('solde_courant', 15, 3)->default(0);

            // Classification & État
            $table->enum('categorie', ['comptoir', 'particulier', 'professionnel', 'societe'])->default('societe');
            $table->boolean('statut')->default(true);

            // Commentaires
            $table->text('notes')->nullable();

            // Traçabilité
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // Index séparés au lieu d'index combiné
            $table->index('raison_sociale');
            $table->index('ifu');
            $table->index('rccm');
            $table->index('statut');
        });
    }

    public function down()
    {
        Schema::dropIfExists('clients');
    }
};
