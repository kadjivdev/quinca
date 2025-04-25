<?php

use App\Models\Parametre\Agent;
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
        Schema::create('fournisseur_approvisionnements', function (Blueprint $table) {
            $table->id();
            $table->float("montant", 15, 0);
            $table->foreignId("user_id")
                ->nullable()
                ->constrained("users", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
            $table->foreignId("fournisseur_id")
                ->nullable()
                ->constrained("fournisseurs", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
            $table->text("rejected_by")->nullable();
            $table->text("validated_by")->nullable();
            $table->date("date")->nullable();
            $table->text("document")->nullable();
            $table->enum("source", ["DIRECTION", "AGENT"])->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::dropIfExists('fournisseur_approvisionnements');
    }
};
