// create_stock_depots_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stock_depots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depot_id')->constrained('depots');
            $table->foreignId('article_id')->constrained('articles');
            $table->decimal('quantite_reelle', 15, 3)->default(0);
            $table->decimal('quantite_reservee', 15, 3)->default(0);
            $table->decimal('prix_moyen', 15, 2)->default(0);
            $table->timestamp('date_dernier_mouvement')->nullable();
            $table->timestamp('date_dernier_inventaire')->nullable();
            $table->decimal('seuil_alerte', 15, 3)->nullable();
            $table->decimal('stock_minimum', 15, 3)->nullable();
            $table->decimal('stock_maximum', 15, 3)->nullable();
            $table->string('emplacement')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // Index pour optimiser les requêtes
            $table->index(['depot_id', 'article_id']);
            $table->index('date_dernier_mouvement');
            $table->index('date_dernier_inventaire');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_depots');
    }
};
