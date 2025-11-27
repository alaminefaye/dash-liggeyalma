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
        Schema::create('client_favoris_prestataires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('prestataire_id')->constrained('prestataires')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['client_id', 'prestataire_id']);
            $table->index('client_id');
            $table->index('prestataire_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_favoris_prestataires');
    }
};

