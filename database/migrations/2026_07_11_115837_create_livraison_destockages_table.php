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
        Schema::create('livraison_destockages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_revendeur_id')
                ->nullable()
                ->constrained('facture_revendeurs')
                ->onUpdate("CASCADE")
                ->onDelete("set null");
            $table->foreignId('depot_id')
                ->nullable()
                ->constrained('depots')
                ->onUpdate("CASCADE")
                ->constrained('depots');
            $table->foreignId('depot_dest_id')
                ->nullable()
                ->constrained('depots')
                ->onUpdate("CASCADE")
                ->constrained('depots');
            $table->foreignId('mouvement_stock_id')
                ->nullable()
                ->constrained('stock_mouvements')
                ->onUpdate("CASCADE");
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onUpdate("CASCADE")
                ->onDelete("set null");
            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('users')
                ->onUpdate("CASCADE")
                ->onDelete("set null");
            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->onUpdate("CASCADE")
                ->onDelete("set null");

            $table->string('numero', 20)->unique();
            $table->datetime('date_livraison');
            $table->datetime('date_validation')->nullable();
            $table->enum('statut', ['brouillon', 'valide', 'annule'])->default('brouillon');
            $table->text('notes')->nullable();
            $table->datetime('validated_at')->nullable();
            $table->text("document")->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livraison_destockages');
    }
};
