<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StripePaymentService
{
    private $secretKey;
    private $publicKey;
    private $webhookSecret;
    private $baseUrl = 'https://api.stripe.com/v1';

    public function __construct()
    {
        $this->secretKey = config('services.stripe.secret_key');
        $this->publicKey = config('services.stripe.public_key');
        $this->webhookSecret = config('services.stripe.webhook_secret');
    }

    /**
     * Create a payment intent
     */
    public function initializePayment(array $data)
    {
        try {
            // Convert amount to cents (Stripe uses smallest currency unit)
            $amountInCents = (int) ($data['amount'] * 100);

            $response = Http::withBasicAuth($this->secretKey, '')
                ->asForm()
                ->post($this->baseUrl . '/payment_intents', [
                    'amount' => $amountInCents,
                    'currency' => strtolower($data['currency'] ?? 'xof'),
                    'description' => $data['description'] ?? 'Paiement Liggeyalma - Commande #' . $data['commande_id'],
                    'metadata' => [
                        'transaction_id' => $data['transaction_id'],
                        'commande_id' => $data['commande_id'],
                    ],
                    'automatic_payment_methods' => [
                        'enabled' => true,
                    ],
                ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'client_secret' => $result['client_secret'] ?? null,
                    'payment_intent_id' => $result['id'] ?? null,
                    'public_key' => $this->publicKey,
                    'data' => $result,
                ];
            }

            $error = $response->json();
            return [
                'success' => false,
                'message' => $error['error']['message'] ?? 'Erreur lors de l\'initialisation du paiement Stripe',
            ];
        } catch (\Exception $e) {
            Log::error('Stripe Payment Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur de connexion avec Stripe: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(string $paymentIntentId)
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->get($this->baseUrl . '/payment_intents/' . $paymentIntentId);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'status' => $result['status'] ?? 'unknown',
                    'paid' => ($result['status'] ?? '') === 'succeeded',
                    'data' => $result,
                ];
            }

            return [
                'success' => false,
                'message' => 'Impossible de vérifier le paiement',
            ];
        } catch (\Exception $e) {
            Log::error('Stripe Verification Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle webhook callback
     */
    public function handleWebhook(array $data, string $signature)
    {
        try {
            // Verify webhook signature
            // This is a simplified version - in production, verify the signature properly
            $event = $data;
            
            if (isset($event['type']) && $event['type'] === 'payment_intent.succeeded') {
                $paymentIntent = $event['data']['object'];
                return [
                    'success' => true,
                    'payment_intent_id' => $paymentIntent['id'] ?? null,
                    'status' => 'succeeded',
                    'metadata' => $paymentIntent['metadata'] ?? [],
                ];
            }

            return [
                'success' => false,
                'message' => 'Event type not handled',
            ];
        } catch (\Exception $e) {
            Log::error('Stripe Webhook Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors du traitement du webhook: ' . $e->getMessage(),
            ];
        }
    }
}


