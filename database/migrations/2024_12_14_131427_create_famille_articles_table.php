<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('famille_articles', function (Blueprint $table) {
            $table->id();
            $table->string('code_famille')->unique();
            $table->string('libelle_famille');
            $table->text('description')->nullable();
            $table->enum('methode_valorisation', ['fifo', 'lifo', 'pmp']);
            $table->boolean('statut')->default(true);

            // Clé étrangère pour la relation parent-enfant
            $table->unsignedBigInteger('famille_parent_id')->nullable();
            $table->foreign('famille_parent_id')
                  ->references('id')
                  ->on('famille_articles')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            // Timestamps standards + soft delete
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('famille_parent_id');
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('famille_articles');
    }
};
