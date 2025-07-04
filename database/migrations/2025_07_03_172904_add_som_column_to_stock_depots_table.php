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
        Schema::table('stock_depots', function (Blueprint $table) {
            $table->foreignId("validated_by")
                ->nullable()
                ->constrained("users", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");

            $table->foreignId("livraison")
                ->nullable()
                ->constrained("livraison_clients", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_depots', function (Blueprint $table) {
            $table->dropForeign(["livraison"]);
            $table->dropColumn("livraison");

            $table->dropForeign(["validated_by"]);
            $table->dropColumn("validated_by");
        });
    }
};
