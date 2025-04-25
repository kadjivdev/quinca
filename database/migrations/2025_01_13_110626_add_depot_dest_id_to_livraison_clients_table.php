<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livraison_clients', function (Blueprint $table) {
            $table->unsignedBigInteger('depot_dest_id')->nullable()->after('depot_id');
            $table->foreign('depot_dest_id')
                  ->references('id')
                  ->on('depots')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('livraison_clients', function (Blueprint $table) {
            $table->dropForeign(['depot_dest_id']);
            $table->dropColumn('depot_dest_id');
        });
    }
};
