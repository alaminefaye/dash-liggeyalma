<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WavePaymentService
{
    private $apiKey;
    private $merchantKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.wave.api_key');
        $this->merchantKey = config('services.wave.merchant_key');
        $this->baseUrl = config('services.wave.base_url', 'https://api.wave.com/v1');
    }

    /**
     * Initialize a payment
     */
    public function initializePayment(array $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/payments', [
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'XOF',
                'description' => $data['description'] ?? 'Paiement Liggeyalma',
                'customer' => [
                    'phone_number' => $data['phone_number'],
                    'name' => $data['customer_name'] ?? '',
                ],
                'callback_url' => $data['callback_url'] ?? url('/api/payment/callback/wave'),
                'metadata' => [
                    'transaction_id' => $data['transaction_id'],
                    'commande_id' => $data['commande_id'],
                ],
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'payment_url' => $result['payment_url'] ?? null,
                    'payment_id' => $result['id'] ?? null,
                    'reference' => $result['reference'] ?? null,
                    'data' => $result,
                ];
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Erreur lors de l\'initialisation du paiement Wave',
            ];
        } catch (\Exception $e) {
            Log::error('Wave Payment Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur de connexion avec Wave: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(string $paymentId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/payments/' . $paymentId);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'status' => $result['status'] ?? 'unknown',
                    'paid' => ($result['status'] ?? '') === 'successful',
                    'data' => $result,
                ];
            }

            return [
                'success' => false,
                'message' => 'Impossible de vérifier le paiement',
            ];
        } catch (\Exception $e) {
            Log::error('Wave Verification Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle webhook callback
     */
    public function handleCallback(array $data)
    {
        // Verify webhook signature if provided
        // Process the callback data
        return [
            'success' => true,
            'transaction_id' => $data['metadata']['transaction_id'] ?? null,
            'status' => $data['status'] ?? 'unknown',
        ];
    }
}

