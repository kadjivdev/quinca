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
        Schema::create('ligne_marchandises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marchandise')
                ->nullable()
                ->constrained('marchand_backs')
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");

            $table->foreignId('article_id')
                ->nullable()
                ->constrained('articles')
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
            $table->foreignId('unite_vente_id')
                ->nullable()
                ->constrained('unite_mesures')
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");

            $table->decimal('quantite', 12, 3);
            $table->decimal('prix_unitaire', 12, 3);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ligne_marchandises');
    }
};
