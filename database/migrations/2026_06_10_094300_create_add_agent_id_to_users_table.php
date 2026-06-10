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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId("agent_id")
                ->nullable()
                ->constrained("agents", "id")
                ->onDelete("SET NULL")
                ->after("zone_id")
                ->comment("L'agent correspondant à cet utilisateur");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(["agent_id"]);
            $table->dropColumn("agent_id");
        });
    }
};
