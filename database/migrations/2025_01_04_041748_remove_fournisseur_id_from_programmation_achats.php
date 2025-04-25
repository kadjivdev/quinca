<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::table('programmation_achats', function (Blueprint $table) {
    //         $table->dropForeign('programmation_achats_fournisseur_id_foreign');
    //         $table->dropColumn('fournisseur_id');
    //     });
    // }

    // /**
    //  * Reverse the migrations.
    //  */
    // public function down(): void
    // {
    //     Schema::table('programmation_achats', function (Blueprint $table) {
    //         $table->foreignId('fournisseur_id')->after('point_de_vente_id')->nullable()->constrained()->onDelete('cascade');
    //     });
    // }
};
