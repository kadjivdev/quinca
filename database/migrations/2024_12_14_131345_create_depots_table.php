<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('depots', function (Blueprint $table) {
            $table->id();
            $table->string('code_depot')->unique();
            $table->string('libelle_depot');
            $table->text('adresse_depot')->nullable();
            $table->string('tel_depot')->nullable();
            $table->boolean('depot_principal')->default(false);
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('depots');
    }
};
