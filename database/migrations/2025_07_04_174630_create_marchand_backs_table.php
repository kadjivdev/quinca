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
        Schema::create('marchand_backs', function (Blueprint $table) {
            $table->id();
            $table->text("numero");
            $table->date("date");
            $table->text("observation")->nullable();
            $table->text("documents")->nullable();
            $table->foreignId("livraison_id")
                ->nullable()->constrained("livraison_clients", "id")
                ->onUpdate("CASCADE")->onDelete("CASCADE");
            $table->foreignId("created_by")
                ->nullable()->constrained("users", "id")
                ->onUpdate("CASCADE")->onDelete("CASCADE");
            $table->foreignId("validated_by")
                ->nullable()->constrained("users", "id")
                ->onUpdate("CASCADE")->onDelete("CASCADE");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marchand_backs');
    }
};
