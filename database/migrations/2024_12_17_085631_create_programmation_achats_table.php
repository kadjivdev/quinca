<?php

// create_programmation_achats_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgrammationAchatsTable extends Migration
{
    public function up()
    {
        Schema::create('programmation_achats', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->date('date_programmation');
            $table->foreignId('point_de_vente_id')->constrained('point_de_ventes');
            $table->foreignId('fournisseur_id')->constrained('fournisseurs');
            $table->text('commentaire')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index('date_programmation');
            $table->index(['point_de_vente_id', 'fournisseur_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('programmation_achats');
    }
}
