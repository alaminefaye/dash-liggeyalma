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
        Schema::create('litiges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('prestataire_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['qualite', 'ponctualite', 'prix', 'comportement', 'autre'])->default('autre');
            $table->text('description');
            $table->json('preuves')->nullable(); // Photos, messages, etc.
            $table->enum('statut', ['en_attente', 'en_cours', 'resolu', 'clos'])->default('en_attente');
            $table->text('resolution')->nullable();
            $table->enum('decision', ['remboursement', 'remediation', 'rejet', 'en_attente'])->nullable();
            $table->decimal('montant_remboursement', 10, 2)->nullable();
            $table->foreignId('traite_par')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('traite_le')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('litiges');
    }
};
