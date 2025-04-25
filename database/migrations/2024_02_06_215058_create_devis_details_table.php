Has<?php

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
        Schema::create('devis_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devis_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('article_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->float('qte_cmde');
            $table->float('prix_unit', 15, 2);
            $table->foreignId('unite_mesure_id')->constrained()->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devis_details');
    }
};
