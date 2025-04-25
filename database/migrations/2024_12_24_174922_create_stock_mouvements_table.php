// create_stock_mouvements_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stock_mouvements', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->timestamp('date_mouvement');
            $table->enum('type_mouvement', ['ENTREE', 'SORTIE', 'TRANSFERT', 'AJUSTEMENT', 'RETOUR']);
            $table->foreignId('depot_id')->constrained('depots');
            $table->foreignId('article_id')->constrained('articles');
            $table->foreignId('unite_mesure_id')->constrained('unite_mesures');
            $table->decimal('quantite', 15, 3);
            $table->decimal('prix_unitaire', 15, 2);
            $table->string('document_type')->nullable();
            $table->unsignedBigInteger('document_id')->nullable();
            $table->foreignId('depot_source_id')->nullable()->constrained('depots');
            $table->foreignId('depot_dest_id')->nullable()->constrained('depots');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // Index pour optimiser les requêtes
            $table->index('code');
            $table->index('date_mouvement');
            $table->index(['document_type', 'document_id']);
            $table->index(['depot_id', 'article_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_mouvements');
    }
};
