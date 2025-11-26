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
        Schema::create('avis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commandes')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('prestataire_id')->constrained('prestataires')->onDelete('cascade');
            $table->integer('note')->default(5); // 1 à 5
            $table->text('commentaire')->nullable();
            $table->json('photos')->nullable();
            $table->json('criteres')->nullable(); // ponctualite, qualite, professionnalisme, rapport_qualite_prix
            $table->text('reponse_prestataire')->nullable();
            $table->timestamp('date_reponse')->nullable();
            $table->timestamps();
            
            $table->index('prestataire_id');
            $table->index('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avis');
    }
};
