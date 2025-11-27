<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrangeMoneyPaymentService
{
    private $merchantId;
    private $merchantKey;
    private $baseUrl;

    public function __construct()
    {
        $this->merchantId = config('services.orange_money.merchant_id');
        $this->merchantKey = config('services.orange_money.merchant_key');
        $this->baseUrl = config('services.orange_money.base_url', 'https://api.orange.com/orange-money-webpay');
    }

    /**
     * Initialize a payment
     */
    public function initializePayment(array $data)
    {
        try {
            // Generate order ID
            $orderId = 'OM-' . time() . '-' . $data['transaction_id'];

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->merchantId . ':' . $this->merchantKey),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/api/v1/webpayment', [
                'merchant_key' => $this->merchantKey,
                'currency' => $data['currency'] ?? 'XOF',
                'order_id' => $orderId,
                'amount' => $data['amount'],
                'return_url' => $data['return_url'] ?? url('/api/payment/callback/orange_money'),
                'cancel_url' => $data['cancel_url'] ?? url('/api/payment/callback/orange_money'),
                'notif_url' => $data['callback_url'] ?? url('/api/payment/callback/orange_money'),
                'lang' => $data['lang'] ?? 'fr',
                'reference' => $data['reference'] ?? $orderId,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'payment_url' => $result['payment_url'] ?? null,
                    'payment_id' => $result['pay_token'] ?? $orderId,
                    'reference' => $orderId,
                    'data' => $result,
                ];
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Erreur lors de l\'initialisation du paiement Orange Money',
            ];
        } catch (\Exception $e) {
            Log::error('Orange Money Payment Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur de connexion avec Orange Money: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(string $orderId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->merchantId . ':' . $this->merchantKey),
            ])->get($this->baseUrl . '/api/v1/transactionstatus', [
                'order_id' => $orderId,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'status' => $result['status'] ?? 'unknown',
                    'paid' => ($result['status'] ?? '') === 'SUCCESS',
                    'data' => $result,
                ];
            }

            return [
                'success' => false,
                'message' => 'Impossible de vérifier le paiement',
            ];
        } catch (\Exception $e) {
            Log::error('Orange Money Verification Error: ' . $e->getMessage());
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
            'order_id' => $data['order_id'] ?? null,
            'status' => $data['status'] ?? 'unknown',
        ];
    }
}

