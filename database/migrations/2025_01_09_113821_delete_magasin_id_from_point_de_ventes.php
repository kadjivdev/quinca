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
        Schema::table('point_de_ventes', function (Blueprint $table) {
            $table->dropForeign('point_de_ventes_depot_id_foreign');
            $table->dropColumn('depot_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('point_de_ventes', function (Blueprint $table) {
            //
        });
    }
};
