<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MTNPaymentService
{
    private $apiKey;
    private $apiSecret;
    private $subscriptionKey;
    private $baseUrl;
    private $environment; // 'sandbox' or 'production'

    public function __construct()
    {
        $this->apiKey = config('services.mtn.api_key');
        $this->apiSecret = config('services.mtn.api_secret');
        $this->subscriptionKey = config('services.mtn.subscription_key');
        $this->environment = config('services.mtn.environment', 'sandbox');
        $this->baseUrl = $this->environment === 'production' 
            ? 'https://api.momodeveloper.mtn.com'
            : 'https://sandbox.momodeveloper.mtn.com';
    }

    /**
     * Get access token
     */
    private function getAccessToken()
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->post($this->baseUrl . '/collection/token/', [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->successful()) {
                $result = $response->json();
                return $result['access_token'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('MTN Token Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Initialize a payment
     */
    public function initializePayment(array $data)
    {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Impossible d\'obtenir le token d\'accès MTN',
                ];
            }

            $externalId = 'MTN-' . time() . '-' . $data['transaction_id'];
            $referenceId = 'REF-' . $data['transaction_id'];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'X-Target-Environment' => $this->environment,
                'X-Reference-Id' => $referenceId,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/collection/v1_0/requesttopay', [
                'amount' => (string) $data['amount'],
                'currency' => $data['currency'] ?? 'XOF',
                'externalId' => $externalId,
                'payer' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => $data['phone_number'],
                ],
                'payerMessage' => $data['description'] ?? 'Paiement Liggeyalma',
                'payeeNote' => 'Commande #' . $data['commande_id'],
            ]);

            if ($response->successful() || $response->status() === 202) {
                return [
                    'success' => true,
                    'payment_id' => $referenceId,
                    'reference' => $externalId,
                    'data' => [
                        'reference_id' => $referenceId,
                        'external_id' => $externalId,
                    ],
                ];
            }

            $errorMessage = $response->json()['message'] ?? 'Erreur lors de l\'initialisation du paiement MTN';
            return [
                'success' => false,
                'message' => $errorMessage,
            ];
        } catch (\Exception $e) {
            Log::error('MTN Payment Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur de connexion avec MTN: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(string $referenceId)
    {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Impossible d\'obtenir le token d\'accès MTN',
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'X-Target-Environment' => $this->environment,
            ])->get($this->baseUrl . '/collection/v1_0/requesttopay/' . $referenceId);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'status' => $result['status'] ?? 'unknown',
                    'paid' => ($result['status'] ?? '') === 'SUCCESSFUL',
                    'data' => $result,
                ];
            }

            return [
                'success' => false,
                'message' => 'Impossible de vérifier le paiement',
            ];
        } catch (\Exception $e) {
            Log::error('MTN Verification Error: ' . $e->getMessage());
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
        return [
            'success' => true,
            'reference_id' => $data['referenceId'] ?? null,
            'status' => $data['status'] ?? 'unknown',
        ];
    }
}


