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
        Schema::create('destockages', function (Blueprint $table) {
            $table->id();
            $table->string("code")->unique();
            $table->string("reference")->nullable()->unique();
            $table->foreignId("depot_id")
                ->nullable()
                ->constrained("depots", "id")
                ->onDelete("set null");

            $table->foreignId("client_id")
                ->nullable()
                ->constrained("clients", "id")
                ->onDelete("set null");

            $table->date("date_op")->nullable();
            $table->text("Observation")->nullable();

            $table->foreignId("created_by")
                ->nullable()
                ->constrained("users", "id")
                ->onDelete("set null");

            $table->foreignId("validated_by")
                ->nullable()
                ->constrained("users", "id")
                ->onDelete("set null");
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
        Schema::dropIfExists('destockages');
    }
};
