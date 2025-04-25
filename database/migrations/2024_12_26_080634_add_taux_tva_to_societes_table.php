<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('societes', function (Blueprint $table) {
            $table->decimal('taux_tva', 5, 2)
                  ->default(18)
                  ->after('pays')
                  ->comment('Taux TVA en pourcentage');
        });
    }

    public function down()
    {
        Schema::table('societes', function (Blueprint $table) {
            $table->dropColumn('taux_tva');
        });
    }
};
