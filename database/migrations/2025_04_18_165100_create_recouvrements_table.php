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
        Schema::create('recouvrements', function (Blueprint $table) {
            $table->id();
            $table->foreignId("client_id")
                ->nullable()
                ->constrained("clients", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
            $table->text("comments")->nullable();
            $table->boolean("verified")->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recouvrements');
    }
};
