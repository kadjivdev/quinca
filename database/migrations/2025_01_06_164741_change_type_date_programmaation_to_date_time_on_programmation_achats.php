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
        Schema::table('programmation_achats', function (Blueprint $table) {
            $table->dateTime('date_programmation')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programmation_achats', function (Blueprint $table) {
            $table->date('date_programmation')->change();
        });
    }
};
