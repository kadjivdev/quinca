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
        Schema::create('articles_fournisseurrequetes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requete_id')
                ->nullable()
                ->constrained("requete_fournisseurs", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
            $table->foreignId('article_id')
                ->nullable()
                ->constrained("articles", "id")
                ->onDelete("CASCADE");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles_fournisseurrequetes');
    }
};
