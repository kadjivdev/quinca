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
        Schema::create('versements', function (Blueprint $table) {
            $table->id();

            $table->string("reference")->nullable();
            $table->string("reference_op")->unique();

            $table->foreignId("client_id")
                ->nullable()
                ->constrained("clients")
                ->onUpdate("CASCADE")
                ->onDelete("set null");

            $table->date("date_op")->nullable();
            $table->enum("type_op", ["Chèque", "MoMo"])->nullable();
            $table->decimal("montant", 15, 2);
            $table->date("date_valeur")
                ->nullable()
                ->comment("Date à laquelle le chèque a été déposé à la banque");

            $table->text("comment")
                ->nullable()
                ->comment("Commentaire lié au versement");

            $table->text("extourned_comment")
                ->nullable()
                ->comment("Commentaire lié au versement extournés");

            $table->string("banque")->nullable();
            $table->string("preuve")->nullable();

            $table->foreignId("created_by")
                ->nullable()
                ->constrained("users")
                ->onUpdate("CASCADE")
                ->onDelete("set null");

            $table->foreignId("validated_by")
                ->nullable()
                ->constrained("users")
                ->onUpdate("CASCADE")
                ->onDelete("set null");

            $table->foreignId("deleted_by")
                ->nullable()
                ->constrained("users")
                ->onUpdate("CASCADE")
                ->onDelete("set null");

            $table->foreignId("extourned_by")
                ->nullable()
                ->constrained("users")
                ->onUpdate("CASCADE")
                ->onDelete("set null");

            $table->timestamp("validated_at")->nullable();
            $table->timestamp("extourned_at")->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('versements');
    }
};
