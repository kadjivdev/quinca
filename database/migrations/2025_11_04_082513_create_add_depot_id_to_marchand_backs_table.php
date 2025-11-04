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
        Schema::table('marchand_backs', function (Blueprint $table) {
            $table->foreignId("depot_id")
                ->after("livraison_id")
                ->nullable()->constrained("depots", "id")
                ->onUpdate("CASCADE")->onDelete("CASCADE");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marchand_backs', function (Blueprint $table) {
            //
        });
    }
};
