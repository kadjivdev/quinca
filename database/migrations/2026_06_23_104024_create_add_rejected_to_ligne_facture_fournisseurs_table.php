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
        Schema::table('ligne_facture_fournisseurs', function (Blueprint $table) {
            $table->boolean("rejected")
                ->default(false)
                ->before("article_id")
                ->comment("Le booleen précisant si cet article a été rejeté ou pas");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ligne_facture_fournisseurs', function (Blueprint $table) {
            $table->dropColumn("rejected");
        });
    }
};
