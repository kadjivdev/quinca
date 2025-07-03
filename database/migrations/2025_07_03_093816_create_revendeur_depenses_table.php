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
        Schema::create('revendeur_depenses', function (Blueprint $table) {
            $table->id();
            $table->text("numero");
            $table->date("day");
            $table->decimal("amount", 8, 2);
            $table->foreignId("depot_id")
                ->nullable()->constrained("depots", "id")
                ->onUpdate("CASCADE")->onDelete("CASCADE");
            $table->foreignId("created_by")
                ->nullable()->constrained("users", "id")
                ->onUpdate("CASCADE")->onDelete("CASCADE");
            $table->foreignId("validated_by")
                ->nullable()->constrained("users", "id")
                ->onUpdate("CASCADE")->onDelete("CASCADE");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revendeur_depenses');
    }
};
