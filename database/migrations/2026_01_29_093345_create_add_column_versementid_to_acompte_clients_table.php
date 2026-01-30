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
        Schema::table('acompte_clients', function (Blueprint $table) {
            $table->foreignId('versement_id')
                ->nullable()
                ->constrained('versements')
                ->onUpdate("CASCADE")
                ->onDelete("set null");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acompte_clients', function (Blueprint $table) {
            $table->dropForeign(["versement_id"]);
            $table->dropColumn("versement_id");
        });
    }
};
