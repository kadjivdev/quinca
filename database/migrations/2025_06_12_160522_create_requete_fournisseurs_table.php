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
        Schema::create('requete_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('num_demande');
            $table->Integer('montant');
            $table->date('date_demande');
            $table->text('nature');
            $table->text('mention');
            $table->text('formulation');
            $table->string('fichier')->nullable();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained("users", "id")
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('fournisseur_id')
                ->nullable()
                ->constrained("fournisseurs", "id")
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('motif')->nullable();
            $table->text('motif_content')->nullable();
            $table->foreignId("validator")
                ->nullable()
                ->constrained("users", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
            $table->timestamp("validate_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requete_fournisseurs');
    }
};
