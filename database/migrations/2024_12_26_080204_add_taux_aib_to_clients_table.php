<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->decimal('taux_aib', 5, 2)
                  ->default(0)
                  ->nullable()
                  ->after('solde_courant')
                  ->comment('Taux AIB en pourcentage');
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('taux_aib');
        });
    }
};
