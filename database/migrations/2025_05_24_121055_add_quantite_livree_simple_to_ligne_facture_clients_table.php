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
        Schema::table('ligne_facture_clients', function (Blueprint $table) {
            $table->decimal('quantite_livree_simple', 10, 2)
                ->nullable()
                ->after('quantite_livree')
                ->comment('Total de quantité considérée à chaque vente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ligne_facture_clients', function (Blueprint $table) {
            //
        });
    }
};
