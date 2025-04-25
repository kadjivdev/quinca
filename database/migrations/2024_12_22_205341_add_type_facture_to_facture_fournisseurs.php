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
        Schema::table('facture_fournisseurs', function (Blueprint $table) {
            $table->enum('type_facture', ['SIMPLE', 'NORMALISE'])->default('SIMPLE');
            $table->decimal('taux_tva', 5, 2)->default(0);
            $table->decimal('taux_aib', 5, 2)->default(0);
        });
    }

    public function down()
    {
        Schema::table('facture_fournisseurs', function (Blueprint $table) {
            $table->dropColumn(['type_facture', 'taux_tva', 'taux_aib']);
        });
    }
};
