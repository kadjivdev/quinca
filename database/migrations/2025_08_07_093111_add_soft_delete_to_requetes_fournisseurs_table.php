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
        Schema::table('requete_fournisseurs', function (Blueprint $table) {
            $table->softDeletes();
            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users', "id")
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requete_fournisseurs', function (Blueprint $table) {
            $table->dropForeign(["deleted_by"]);
            $table->dropColumn(["deleted_by", "deleted_at"]);
        });
    }
};
