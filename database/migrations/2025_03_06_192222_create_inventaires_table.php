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
        Schema::create('inventaires', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date_inventaire')->nullable();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained("users", "id")
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // $table->foreignId('depot_id')->constrained("depots","id")->onUpdate('cascade')->onDelete('cascade');
            $table->text('depot_ids')->nullable();
            $table->foreignId('validator_id')
                ->nullable()
                ->constrained('users', 'id')
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
            $table->dateTime('validated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaires');
    }
};
