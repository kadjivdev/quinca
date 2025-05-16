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
            $table->foreignId('updated_by')->after('validated_by')->nullable()->constrained('users');
            $table->foreignId('annule_par')->after('updated_by')->nullable()->constrained('users');
            $table->timestamp('date_annulation')->after('validated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reglement_clients', function (Blueprint $table) {
            $table->dropForeign('updated_by');
            $table->dropColumn('updated_by');

            $table->dropForeign('annule_par');
            $table->dropColumn('annule_par');
            
            $table->dropColumn('date_annulation');
        });
    }
};
