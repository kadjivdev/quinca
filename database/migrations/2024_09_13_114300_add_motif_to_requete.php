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
            $foreignKeys = DB::select("
                SELECT motif 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'requetes' 
                AND CONSTRAINT_TYPE = 'motif'
            ");

            // Drop each foreign key
            foreach ($foreignKeys as $foreignKey) {
                $table->dropForeign($foreignKey->CONSTRAINT_NAME);
            }

            $table->dropColumn(['motif', 'motif_content', 'validate_at']);

            // Foreignkey
            $table->dropForeign(["validator"]);
            $table->dropColumn("validator");
        });
    }
};
