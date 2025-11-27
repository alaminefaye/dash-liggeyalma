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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('methode_paiement'); // 'wave', 'orange_money', 'mtn', 'stripe'
            $table->string('payment_id')->nullable()->after('provider'); // ID du paiement externe
            $table->text('payment_url')->nullable()->after('payment_id'); // URL de paiement (pour redirection)
            $table->text('client_secret')->nullable()->after('payment_url'); // Pour Stripe
            $table->string('phone_number')->nullable()->after('client_secret'); // Pour Mobile Money
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['provider', 'payment_id', 'payment_url', 'client_secret', 'phone_number']);
        });
    }
};


