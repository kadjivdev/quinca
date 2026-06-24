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
        Schema::create('destockage_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId("destockage_id")
                ->nullable()
                ->constrained("destockages", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");

            $table->foreignId("article_id")
                ->nullable()
                ->constrained("articles", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");

            $table->foreignId("unite_mesure_id")
                ->nullable()
                ->constrained("unite_mesures", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");

            $table->decimal("montant", 15, 2)->nullable();
            $table->decimal("qte", 15, 2)->nullable();

            $table->decimal("pu", 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destockage_lignes');
    }
};
