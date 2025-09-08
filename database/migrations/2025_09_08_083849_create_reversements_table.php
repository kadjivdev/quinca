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
        Schema::create('reversements', function (Blueprint $table) {
            $table->id();
            $table->date('date_recette')->nullable();
            $table->foreignId('depot_id')
                ->nullable()
                ->constrained('depots', "id")
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->float('recette')->default(0);
            $table->float('depense')->default(0);
            $table->float('recette_to_reverse')->default(0);
            $table->float('montant_reversed')->default(0);
            $table->string("commentaire")->nullable();
            $table->string("preuve")->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users', "id")
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users', "id")
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('users', "id")
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->timestamp("validated_at")->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reversements');
    }
};
