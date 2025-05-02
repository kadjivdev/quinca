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
        Schema::create('requetes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('num_demande');
            $table->Integer('montant');
            $table->date('date_demande');
            $table->text('nature');
            $table->text('mention');
            $table->text('formulation');
            $table->string('fichier')->nullable();
            $table->foreignId('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requetes');
    }
};
