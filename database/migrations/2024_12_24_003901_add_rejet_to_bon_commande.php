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
        Schema::table('bon_commandes', function (Blueprint $table) {
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
        Schema::table('bon_commandes', function (Blueprint $table) {
            $table->dropColumn('motif_rejet');
            $table->dropForeign(['rejected_by']);
            $table->dropColumn('rejected_by');
            $table->dropColumn('rejected_at');
        });
    }
};
