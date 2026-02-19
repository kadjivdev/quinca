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
        Schema::create('requete_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->nullable();
            $table->foreignId('depot_id')
                ->nullable()
                ->constrained("depots")
                ->onUpdate('CASCADE')
                ->onDelete('set null');
            $table->foreignId('article_id')
                ->nullable()
                ->constrained("articles")
                ->onUpdate('CASCADE')
                ->onDelete('set null');
            $table->foreignId('unite_mesure_id')
                ->nullable()
                ->constrained("unite_mesures")
                ->onUpdate('CASCADE')
                ->onDelete('set null');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained("users")
                ->onUpdate('CASCADE')
                ->onDelete('set null');
            $table->foreignId('validated_by')
                ->nullable()
                ->constrained("users")
                ->onUpdate('CASCADE')
                ->onDelete('set null');
            $table->decimal("quantite", 8, 2);
            $table->text('commentaire')->nullable();
            $table->string('preuve')->nullable();
            $table->date("validated_at")->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requete_stocks');
    }
};
