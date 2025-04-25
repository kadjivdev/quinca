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
        Schema::table('session_caisses', function (Blueprint $table) {
            $table->foreignId('point_de_vente_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_caisses', function (Blueprint $table) {
            $table->dropForeign('point_de_vente_id');
            $table->dropColumn('point_de_vente_id');
        });
    }
};
