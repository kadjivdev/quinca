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
        Schema::table('reglement_clients', function (Blueprint $table) {
            $table->foreignId('facture_client_id')->nullable()->change();
            $table->foreignId('facture_revendeur_id')
                ->nullable()->constrained('facture_revendeurs')
                ->onUpdate("CASCADE")->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reglement_clients', function (Blueprint $table) {
            $table->dropForeign(["facture_revendeur_id"]);
            $table->dropColumn("facture_revendeur_id");
        });
    }
};
