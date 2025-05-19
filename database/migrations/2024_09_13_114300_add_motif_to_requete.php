<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            $table->string('motif')->nullable();
            $table->text('motif_content')->nullable();
            $table->foreignId("validator")
                ->nullable()
                ->constrained("users", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
            $table->timestamp("validate_at")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            // Get all foreign keys for this table
            $table->dropColumn(['motif', 'motif_content', 'validate_at']);

            // Foreignkey
            $table->dropForeign(["validator"]);
            $table->dropColumn("validator");
        });
    }
};
