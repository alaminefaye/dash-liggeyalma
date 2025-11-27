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
        Schema::create('prestataire_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestataire_id')->constrained('prestataires')->onDelete('cascade');
            $table->foreignId('commande_id')->nullable()->constrained('commandes')->onDelete('set null');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('accuracy', 8, 2)->nullable(); // Précision en mètres
            $table->decimal('speed', 8, 2)->nullable(); // Vitesse en m/s
            $table->decimal('heading', 5, 2)->nullable(); // Direction en degrés
            $table->timestamp('timestamp');
            $table->timestamps();
            
            $table->index(['prestataire_id', 'commande_id']);
            $table->index('timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestataire_positions');
    }
};

