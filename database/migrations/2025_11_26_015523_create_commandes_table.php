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
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('prestataire_id')->nullable()->constrained('prestataires')->onDelete('set null');
            $table->foreignId('categorie_service_id')->constrained('categorie_services')->onDelete('restrict');
            $table->foreignId('sous_categorie_service_id')->nullable()->constrained('sous_categorie_services')->onDelete('set null');
            $table->enum('statut', ['en_attente', 'acceptee', 'en_route', 'arrivee', 'en_cours', 'terminee', 'annulee'])->default('en_attente');
            $table->enum('type_commande', ['immediate', 'programmee'])->default('immediate');
            $table->text('description');
            $table->json('photos')->nullable();
            $table->text('adresse_intervention');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->dateTime('date_heure_souhaitee')->nullable();
            $table->decimal('montant_total', 12, 2)->default(0);
            $table->decimal('montant_commission', 12, 2)->default(0);
            $table->enum('methode_paiement', ['cash', 'mobile_money', 'carte'])->nullable();
            $table->enum('statut_paiement', ['en_attente', 'paye', 'rembourse'])->default('en_attente');
            $table->json('historique_statuts')->nullable(); // Historique des changements de statut
            $table->timestamps();
            
            $table->index('statut');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
