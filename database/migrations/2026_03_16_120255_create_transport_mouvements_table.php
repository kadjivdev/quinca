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
        Schema::create('transport_mouvements', function (Blueprint $table) {
            $table->id();
            $table->string("reference");
            $table->foreignId('transportation_id')
                ->nullable()
                ->constrained('transportations')
                ->cascadeOnUpdate()
                ->nullOnDelete()
                ->comment('Le moyen de transport concerné');

            $table->foreignId('client_id')
                ->nullable()
                ->constrained('clients')
                ->cascadeOnUpdate()
                ->nullOnDelete()
                ->comment('Le client chez qui le camion est parti');

            $table->date('date');
            $table->decimal('montant', 15, 2);

            $table->text('comment')->nullable();
            $table->string('preuve')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamp('validated_at')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_mouvements');
    }
};
