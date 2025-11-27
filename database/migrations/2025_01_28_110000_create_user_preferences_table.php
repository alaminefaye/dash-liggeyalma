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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            
            // Notification preferences
            $table->boolean('new_request')->default(true);
            $table->boolean('order_status')->default(true);
            $table->boolean('new_message')->default(true);
            $table->boolean('payment_received')->default(true);
            $table->boolean('review_received')->default(true);
            
            // App preferences
            $table->string('language')->default('fr'); // 'fr', 'en'
            $table->boolean('dark_mode')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};

