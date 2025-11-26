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
        Schema::create('prestataires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('metier');
            $table->json('specialites')->nullable();
            $table->text('description')->nullable();
            $table->integer('annees_experience')->default(0);
            $table->decimal('tarif_horaire', 10, 2)->nullable();
            $table->json('forfaits')->nullable(); // Prix fixes par type d'intervention
            $table->decimal('frais_deplacement', 10, 2)->default(0);
            $table->json('zone_intervention')->nullable(); // Villes/quartiers
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('rayon_intervention')->default(10); // en km
            $table->decimal('solde', 12, 2)->default(0); // Peut être négatif
            $table->decimal('score_confiance', 3, 2)->default(5.00);
            $table->enum('statut_inscription', ['en_attente', 'valide', 'refuse'])->default('en_attente');
            $table->json('documents')->nullable(); // Pièce identité, certificats, etc.
            $table->boolean('disponible')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestataires');
    }
};
