<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->unsignedBigInteger('unite_mesure_id')->nullable()->after('emplacement_stock');
            $table->foreign('unite_mesure_id')
                  ->references('id')
                  ->on('unite_mesures')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['unite_mesure_id']);
            $table->dropColumn('unite_mesure_id');
        });
    }
};
