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
        Schema::table('facture_clients', function (Blueprint $table) {
            $table->enum("moyen_reglement", ['espece', "cheque", "virement", "carte_bancaire", "MoMo", "Flooz", "Celtis_Pay", "Effet", "Avoir"])
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facture_clients', function (Blueprint $table) {
            $table->removeColumn("moyen_reglement");
        });
    }
};
