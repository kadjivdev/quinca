<?php

// create_ligne_programmation_achats_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLigneProgrammationAchatsTable extends Migration
{
    public function up()
    {
        Schema::create('ligne_programmation_achats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programmation_id')->constrained('programmation_achats')->onDelete('cascade');
            $table->foreignId('article_id')->constrained('articles');
            $table->foreignId('unite_mesure_id')->constrained('unite_mesures');
            $table->decimal('quantite', 10, 2);
            $table->integer('ordre')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['programmation_id', 'article_id']);
            $table->index('ordre');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ligne_programmation_achats');
    }
}
