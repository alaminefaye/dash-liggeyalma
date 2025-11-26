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
        Schema::create('retraits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestataire_id')->constrained('prestataires')->onDelete('cascade');
            $table->decimal('montant', 12, 2);
            $table->decimal('frais_retrait', 12, 2)->default(0);
            $table->decimal('montant_net', 12, 2); // montant - frais
            $table->enum('methode', ['mobile_money', 'virement', 'especes'])->default('mobile_money');
            $table->string('numero_compte'); // Numéro de compte pour le retrait
            $table->enum('statut', ['en_attente', 'valide', 'refuse'])->default('en_attente');
            $table->text('motif_refus')->nullable();
            $table->timestamp('date_validation')->nullable();
            $table->foreignId('valide_par')->nullable()->constrained('users')->onDelete('set null'); // Admin qui a validé
            $table->timestamps();
            
            $table->index('prestataire_id');
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retraits');
    }
};
