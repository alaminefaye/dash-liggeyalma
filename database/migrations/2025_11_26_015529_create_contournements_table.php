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
        Schema::create('contournements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestataire_id')->constrained('prestataires')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('commande_id')->nullable()->constrained('commandes')->onDelete('set null');
            $table->enum('type', ['paiement_direct', 'partage_numero', 'prix_non_enregistre', 'communication_hors_app'])->default('paiement_direct');
            $table->text('description');
            $table->json('preuves')->nullable(); // Logs, conversations, etc.
            $table->enum('statut', ['detecte', 'confirme', 'rejete'])->default('detecte');
            $table->text('sanction_appliquee')->nullable();
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
        Schema::dropIfExists('contournements');
    }
};
