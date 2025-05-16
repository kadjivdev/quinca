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
            $table->foreignId('depot_id')
                ->nullable()
                ->constrained('depots')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE')
                ->comment('ID de l\'article concerné');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarifications', function (Blueprint $table) {
            $table->dropForeign(["depot_id"]);
            $table->dropColumn("depot_id");
        });
    }
};
