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
            $table->foreignId('encaissed_by')->after('updated_by')->nullable()->constrained('users');
            $table->timestamp('encaissed_at')->after('encaissed_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facture_clients', function (Blueprint $table) {
            //
        });
    }
};
