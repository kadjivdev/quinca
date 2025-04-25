<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('point_de_ventes', function (Blueprint $table) {
            $table->foreignId('depot_id')->nullable()->constrained('depots')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('point_de_ventes', function (Blueprint $table) {
            $table->dropForeign(['depot_id']);
            $table->dropColumn('depot_id');
        });
    }

};
