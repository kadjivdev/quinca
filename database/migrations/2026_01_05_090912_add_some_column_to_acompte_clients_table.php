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
        Schema::table('acompte_clients', function (Blueprint $table) {
            $table->enum('type_paiement', ['espece', 'virement', 'cheque', 'autres'])->default('espece')->change();
            $table->string('preuve')->nullable()->after('montant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acompte_clients', function (Blueprint $table) {
            $table->enum('type_paiement', ['espece', 'virement', 'cheque'])->default('espece')->change();
            $table->dropColumn('preuve');
        });
    }
};
