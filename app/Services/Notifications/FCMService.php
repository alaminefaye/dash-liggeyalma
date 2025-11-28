<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\CredentialsLoader;
use Google\Auth\OAuth2;

class FCMService
{
    private $projectId;
    private $apiUrl;
    private $credentialsPath;
    private $accessToken;

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id', 'depannema-288ba');
        $this->apiUrl = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
        $this->credentialsPath = config('services.firebase.credentials');
        
        if (empty($this->credentialsPath) || !file_exists($this->credentialsPath)) {
            Log::warning('FCM credentials file not found: ' . $this->credentialsPath . '. Push notifications will not work.');
        }
    }

    /**
     * Send notification to a single device
     */
    public function sendToDevice(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        if (empty($this->credentialsPath) || !file_exists($this->credentialsPath)) {
            return false;
        }

        $message = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $this->formatData($data),
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'notification_count' => 1,
                    ],
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ],
                    ],
                ],
            ],
        ];

        return $this->sendRequest($message);
    }

    /**
     * Send notification to multiple devices
     */
    public function sendToMultipleDevices(array $fcmTokens, string $title, string $body, array $data = []): array
    {
        if (empty($this->credentialsPath) || !file_exists($this->credentialsPath)) {
            return ['success' => false, 'message' => 'FCM credentials not configured'];
        }

        $results = [];
        foreach ($fcmTokens as $token) {
            $results[$token] = $this->sendToDevice($token, $title, $body, $data);
        }

        return $results;
    }

    /**
     * Send notification to a user (all their devices)
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): array
    {
        // Check user preferences
        $preferences = DB::table('user_preferences')
            ->where('user_id', $userId)
            ->first();

        // Check notification type and user preferences
        $notificationType = $data['type'] ?? 'general';
        $shouldNotify = true;

        if ($preferences) {
            switch ($notificationType) {
                case 'new_request':
                    $shouldNotify = (bool) $preferences->new_request;
                    break;
                case 'order_status':
                    $shouldNotify = (bool) $preferences->order_status;
                    break;
                case 'new_message':
                    $shouldNotify = (bool) $preferences->new_message;
                    break;
                case 'payment_received':
                    $shouldNotify = (bool) $preferences->payment_received;
                    break;
                case 'review_received':
                    $shouldNotify = (bool) $preferences->review_received;
                    break;
            }
        }

        if (!$shouldNotify) {
            return ['success' => false, 'message' => 'User has disabled this notification type'];
        }

        // Get all active FCM tokens for this user
        $tokens = DB::table('fcm_tokens')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No active FCM tokens found for user'];
        }

        return $this->sendToMultipleDevices($tokens, $title, $body, $data);
    }

    /**
     * Send notification for new order
     */
    public function sendNewOrderNotification(int $prestataireUserId, array $commandeData): bool
    {
        $title = 'Nouvelle demande de service';
        $body = "Une nouvelle demande a été créée pour votre service";
        
        if (isset($commandeData['categorie'])) {
            $body = "Nouvelle demande : " . $commandeData['categorie'];
        }

        $data = [
            'type' => 'new_request',
            'commande_id' => (string)($commandeData['id'] ?? ''),
            'action' => 'open_commande',
        ];

        $results = $this->sendToUser($prestataireUserId, $title, $body, $data);
        return !empty($results) && (!isset($results['success']) || $results['success'] !== false);
    }

    /**
     * Send notification for order status update
     */
    public function sendOrderStatusNotification(int $userId, string $status, array $commandeData): bool
    {
        $statusMessages = [
            'acceptee' => 'Votre demande a été acceptée',
            'en_route' => 'Le prestataire est en route',
            'arrivee' => 'Le prestataire est arrivé',
            'en_cours' => 'La prestation est en cours',
            'terminee' => 'La prestation est terminée',
            'annulee' => 'La demande a été annulée',
        ];

        $title = 'Mise à jour de votre commande';
        $body = $statusMessages[$status] ?? 'Statut de votre commande mis à jour';

        $data = [
            'type' => 'order_status',
            'commande_id' => (string)($commandeData['id'] ?? ''),
            'status' => $status,
            'action' => 'open_commande',
        ];

        $results = $this->sendToUser($userId, $title, $body, $data);
        return !empty($results) && (!isset($results['success']) || $results['success'] !== false);
    }

    /**
     * Send notification for new message
     */
    public function sendNewMessageNotification(int $userId, string $senderName, string $message, int $conversationId): bool
    {
        $title = $senderName;
        $body = $message;

        $data = [
            'type' => 'new_message',
            'conversation_id' => (string)$conversationId,
            'action' => 'open_chat',
        ];

        $results = $this->sendToUser($userId, $title, $body, $data);
        return !empty($results) && (!isset($results['success']) || $results['success'] !== false);
    }

    /**
     * Send notification for payment received
     */
    public function sendPaymentNotification(int $userId, float $amount, array $transactionData): bool
    {
        $title = 'Paiement reçu';
        $body = "Vous avez reçu " . number_format($amount, 0, ',', ' ') . " FCFA";

        $data = [
            'type' => 'payment_received',
            'transaction_id' => (string)($transactionData['id'] ?? ''),
            'amount' => (string)$amount,
            'action' => 'open_transaction',
        ];

        $results = $this->sendToUser($userId, $title, $body, $data);
        return !empty($results) && (!isset($results['success']) || $results['success'] !== false);
    }

    /**
     * Send notification for new review
     */
    public function sendReviewNotification(int $prestataireId, string $clientName, float $rating): bool
    {
        $title = 'Nouvel avis reçu';
        $body = "{$clientName} vous a donné une note de {$rating}/5";

        $data = [
            'type' => 'review_received',
            'action' => 'open_reviews',
        ];

        $results = $this->sendToUser($prestataireId, $title, $body, $data);
        return !empty($results) && (!isset($results['success']) || $results['success'] !== false);
    }

    /**
     * Get access token from service account credentials
     */
    private function getAccessToken(): ?string
    {
        if (!empty($this->accessToken)) {
            return $this->accessToken;
        }

        try {
            if (empty($this->credentialsPath) || !file_exists($this->credentialsPath)) {
                Log::error('FCM credentials file not found: ' . $this->credentialsPath);
                return null;
            }

            $credentials = json_decode(file_get_contents($this->credentialsPath), true);
            
            if (!isset($credentials['client_email']) || !isset($credentials['private_key'])) {
                Log::error('Invalid FCM credentials file format');
                return null;
            }

            // Use Google Auth library to get access token
            $scope = 'https://www.googleapis.com/auth/firebase.messaging';
            
            $oauth2 = new OAuth2([
                'audience' => 'https://oauth2.googleapis.com/token',
                'issuer' => $credentials['client_email'],
                'scope' => $scope,
                'signingAlgorithm' => 'RS256',
                'signingKey' => $credentials['private_key'],
                'tokenCredentialUri' => 'https://oauth2.googleapis.com/token',
            ]);

            $token = $oauth2->fetchAuthToken();
            
            if (isset($token['access_token'])) {
                $this->accessToken = $token['access_token'];
                return $this->accessToken;
            }

            Log::error('Failed to get FCM access token: ' . json_encode($token));
            return null;
        } catch (\Exception $e) {
            Log::error('FCM access token error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Format data for FCM message (all values must be strings)
     */
    private function formatData(array $data): array
    {
        $formatted = [];
        foreach ($data as $key => $value) {
            $formatted[$key] = is_string($value) ? $value : json_encode($value);
        }
        return $formatted;
    }

    /**
     * Send HTTP request to FCM V1 API
     */
    private function sendRequest(array $payload): bool
    {
        try {
            $accessToken = $this->getAccessToken();
            
            if (empty($accessToken)) {
                return false;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Check if notification was successful (V1 API returns 'name' on success)
                if (isset($responseData['name'])) {
                    return true;
                }
                
                // Check for errors
                if (isset($responseData['error'])) {
                    $error = $responseData['error'];
                    $errorMessage = $error['message'] ?? 'Unknown error';
                    $errorCode = $error['code'] ?? 'unknown';
                    
                    Log::warning("FCM send failed: {$errorCode} - {$errorMessage}");
                    
                    // Check for invalid token
                    if (str_contains($errorMessage, 'registration-token-not-registered') || 
                        str_contains($errorMessage, 'invalid-argument')) {
                        // Optionally mark token as inactive in database
                        Log::warning("FCM token invalid: {$errorMessage}");
                    }
                    
                    return false;
                }
                
                return true;
            }

            Log::error('FCM request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('FCM service error: ' . $e->getMessage());
            return false;
        }
    }
}

