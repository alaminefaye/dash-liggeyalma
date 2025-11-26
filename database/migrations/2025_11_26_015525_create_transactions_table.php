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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->nullable()->constrained('commandes')->onDelete('set null');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('prestataire_id')->nullable()->constrained('prestataires')->onDelete('set null');
            $table->enum('type', ['paiement', 'retrait', 'commission'])->default('paiement');
            $table->decimal('montant', 12, 2);
            $table->decimal('commission', 12, 2)->default(0);
            $table->enum('methode_paiement', ['cash', 'mobile_money', 'carte', 'wallet'])->nullable();
            $table->enum('statut', ['en_attente', 'validee', 'refusee'])->default('en_attente');
            $table->string('reference_externe')->nullable(); // Référence du paiement externe
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('type');
            $table->index('statut');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
