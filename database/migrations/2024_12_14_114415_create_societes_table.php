<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
   /**
    * Run the migrations.
    */
   public function up(): void
   {
       Schema::create('societes', function (Blueprint $table) {
           $table->id();

           // Informations de base de la société
           $table->string('nom_societe');
           $table->string('raison_sociale')->nullable();
           $table->string('forme_juridique')->nullable();

           // Identifiants légaux
           $table->string('rccm')->nullable()->comment('Registre du Commerce et du Crédit Mobilier');
           $table->string('ifu', 13)->nullable()->comment('Identifiant Fiscal Unique du Bénin');
           $table->string('rib')->nullable()->comment('Relevé d\'Identité Bancaire');

           // Contacts et localisation
           $table->string('email')->nullable();
           $table->string('telephone_1', 20);
           $table->string('telephone_2', 20)->nullable();
           $table->text('adresse')->nullable();
           $table->string('ville')->nullable();
           $table->string('pays')->default('Bénin');

           // Description et médias
           $table->text('description')->nullable();
           $table->string('logo_path')->nullable()->comment('Chemin vers le logo de l\'entreprise');
           $table->string('favicon_path')->nullable()->comment('Chemin vers le favicon du site');

           // Paramètres supplémentaires (Facturation, Impression, etc.)
           $table->json('parametres_supplementaires')->nullable()->comment('Configurations additionnelles en JSON');

           // Timestamps et soft delete
           $table->timestamps();
           $table->softDeletes();
       });

       // Insertion des données par défaut
       DB::table('societes')->insert([
           'nom_societe' => 'Ma Société',
           'pays' => 'Bénin',
           'telephone_1' => '00000000',
           'parametres_supplementaires' => json_encode([
               'facturation' => [
                   'prefixe_facture' => 'FACT-',
                   'prefixe_bl' => 'BL-',
                   'prefixe_devis' => 'DEV-',
                   'message_defaut_facture' => 'Merci de votre confiance',
                   'conditions_paiement' => 'Paiement à 30 jours',
                   'devise' => 'FCFA',
                   'tva' => '18'
               ],
               'impression' => [
                   'format_papier' => 'A4',
                   'entete_personnalise' => true,
                   'pied_page_personnalise' => true,
                   'afficher_logo' => true,
                   'couleur_principale' => '#000000',
                   'police' => 'Arial'
               ],
               'communication' => [
                   'signature_email' => 'Cordialement,\nL\'équipe',
                   'whatsapp' => '',
                   'facebook' => '',
                   'site_web' => ''
               ]
           ]),
           'created_at' => now(),
           'updated_at' => now()
       ]);
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
       Schema::dropIfExists('societes');
   }
};
