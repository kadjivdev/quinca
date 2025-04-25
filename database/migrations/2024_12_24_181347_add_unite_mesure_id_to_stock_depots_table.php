<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniteMesureIdToStockDepotsTable extends Migration
{
    public function up()
    {
        Schema::table('stock_depots', function (Blueprint $table) {
            $table->unsignedBigInteger('unite_mesure_id')->after('article_id');
            $table->foreign('unite_mesure_id')
                  ->references('id')
                  ->on('unite_mesures')
                  ->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::table('stock_depots', function (Blueprint $table) {
            $table->dropForeign(['unite_mesure_id']);
            $table->dropColumn('unite_mesure_id');
        });
    }
}
