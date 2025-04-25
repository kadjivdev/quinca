<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
   {
       Schema::create('fournisseurs', function (Blueprint $table) {
           $table->id();
           $table->string('code_fournisseur', 20)->unique();
           $table->string('raison_sociale');
           $table->string('nom_commercial')->nullable();
           $table->string('adresse')->nullable();
           $table->string('telephone', 20)->nullable();
           $table->string('email')->nullable()->unique();
           $table->string('ifu', 13)->nullable()->comment('Identifiant Fiscal Unique');
           $table->string('rccm', 50)->nullable()->comment('Registre du Commerce et du Crédit Mobilier');
           $table->text('observations')->nullable();
           $table->boolean('statut')->default(true);
           $table->unsignedBigInteger('created_by')->nullable();
           $table->unsignedBigInteger('updated_by')->nullable();
           $table->timestamps();
           $table->softDeletes(); // Ajoute deleted_at pour la suppression douce

           // Index pour optimiser les recherches
           $table->index('code_fournisseur');
           $table->index('raison_sociale');
           $table->index('statut');
       });
   }

   public function down()
   {
       Schema::dropIfExists('fournisseurs');
   }
};
