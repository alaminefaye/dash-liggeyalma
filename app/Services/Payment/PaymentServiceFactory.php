<?php

namespace App\Services\Payment;

class PaymentServiceFactory
{
    /**
     * Get the appropriate payment service based on provider
     */
    public static function make(string $provider): PaymentServiceInterface
    {
        return match ($provider) {
            'wave' => new WavePaymentService(),
            'orange_money' => new OrangeMoneyPaymentService(),
            'mtn' => new MTNPaymentService(),
            'stripe' => new StripePaymentService(),
            default => throw new \InvalidArgumentException("Provider de paiement non supporté: {$provider}"),
        };
    }
}

interface PaymentServiceInterface
{
    public function initializePayment(array $data): array;
    public function verifyPayment(string $paymentId): array;
}


