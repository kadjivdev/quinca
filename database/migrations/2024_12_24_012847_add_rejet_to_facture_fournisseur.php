<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('facture_fournisseurs', function (Blueprint $table) {
            $table->text('motif_rejet')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamp('rejected_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facture_fournisseurs', function (Blueprint $table) {
            $table->dropForeign(['rejected_by']);
            $table->dropColumn(['motif_rejet','rejected_at','rejected_by']);
        });
    }
};
