<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reglement_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->date('date_reglement');
            $table->foreignId('facture_fournisseur_id')->constrained('facture_fournisseurs')->onDelete('restrict');
            $table->enum('mode_reglement', ['ESPECE', 'CHEQUE', 'VIREMENT', 'DECHARGE', 'AUTRES']);
            $table->string('reference_reglement')->nullable();
            $table->decimal('montant_reglement', 15, 2);
            $table->text('commentaire')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('restrict');

            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'date_reglement', 'mode_reglement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reglement_fournisseurs');
    }
};
