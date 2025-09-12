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
        Schema::table('tarifications', function (Blueprint $table) {
            $table->foreignId("unite_mesure_id")
                ->after("prix")
                ->nullable()
                ->constrained("unite_mesures")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarifications', function (Blueprint $table) {
            //
        });
    }
};
