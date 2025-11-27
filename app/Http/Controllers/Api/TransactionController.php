<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Commande;
use App\Services\Payment\PaymentServiceFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    /**
     * Get user's transactions
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $userType = $user->role;

        $query = Transaction::with(['commande.client.user', 'commande.prestataire.user']);

        if ($userType === 'client') {
            $client = \App\Models\Client::where('user_id', $user->id)->first();
            if ($client) {
                $query->whereHas('commande', function ($q) use ($client) {
                    $q->where('client_id', $client->id);
                });
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Client non trouvé',
                ], 404);
            }
        } elseif ($userType === 'prestataire') {
            $prestataire = \App\Models\Prestataire::where('user_id', $user->id)->first();
            if ($prestataire) {
                $query->whereHas('commande', function ($q) use ($prestataire) {
                    $q->where('prestataire_id', $prestataire->id);
                });
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Prestataire non trouvé',
                ], 404);
            }
        }

        // Filter by status
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        $perPage = $request->get('per_page', 15);
        $transactions = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * Get transaction details
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $transaction = Transaction::with(['commande.client.user', 'commande.prestataire.user'])
            ->findOrFail($id);

        // Vérifier que l'utilisateur a accès à cette transaction
        $userType = $user->role;
        $hasAccess = false;

        if ($userType === 'client') {
            $client = \App\Models\Client::where('user_id', $user->id)->first();
            $hasAccess = $client && $transaction->commande->client_id == $client->id;
        } elseif ($userType === 'prestataire') {
            $prestataire = \App\Models\Prestataire::where('user_id', $user->id)->first();
            $hasAccess = $prestataire && $transaction->commande->prestataire_id == $prestataire->id;
        }

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $transaction->id,
                'commande_id' => $transaction->commande_id,
                'montant' => $transaction->montant,
                'type' => $transaction->type,
                'methode_paiement' => $transaction->methode_paiement,
                'statut' => $transaction->statut,
                'reference' => $transaction->reference_externe,
                'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                'commande' => [
                    'id' => $transaction->commande->id,
                    'description' => $transaction->commande->description,
                    'montant_total' => $transaction->commande->montant_total,
                ],
            ],
        ]);
    }

    /**
     * Initialize payment for a commande
     */
    public function initialize(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'commande_id' => 'required|exists:commandes,id',
            'methode_paiement' => 'required|in:cash,mobile_money,carte',
            'provider' => 'required_if:methode_paiement,mobile_money,carte|in:wave,orange_money,mtn,stripe',
            'phone_number' => 'required_if:methode_paiement,mobile_money|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $commande = Commande::with(['client.user', 'prestataire'])->findOrFail($request->commande_id);

        // Vérifier que l'utilisateur est le client de la commande
        $client = \App\Models\Client::where('user_id', $user->id)->first();
        if (!$client || $commande->client_id != $client->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        // Vérifier que la commande est terminée
        if ($commande->statut !== 'terminee') {
            return response()->json([
                'success' => false,
                'message' => 'La commande doit être terminée pour être payée',
            ], 400);
        }

        // Vérifier qu'il n'y a pas déjà une transaction en cours
        $existingTransaction = Transaction::where('commande_id', $commande->id)
            ->whereIn('statut', ['en_attente', 'en_cours'])
            ->first();

        if ($existingTransaction) {
            return response()->json([
                'success' => false,
                'message' => 'Une transaction est déjà en cours pour cette commande',
            ], 400);
        }

        // Calculer la commission (exemple: 10%)
        $commissionRate = 0.10;
        $commission = $commande->montant_total * $commissionRate;

        // Créer la transaction
        $transaction = Transaction::create([
            'commande_id' => $commande->id,
            'client_id' => $client->id,
            'prestataire_id' => $commande->prestataire_id,
            'montant' => $commande->montant_total,
            'commission' => $commission,
            'type' => 'paiement',
            'methode_paiement' => $request->methode_paiement,
            'statut' => 'en_attente',
            'reference_externe' => 'TXN-' . time() . '-' . $commande->id,
            'provider' => $request->provider ?? null,
            'phone_number' => $request->phone_number ?? null,
        ]);

        // Si c'est un paiement cash, marquer comme payé directement
        if ($request->methode_paiement === 'cash') {
            $transaction->update(['statut' => 'validee']);
            $commande->update(['statut_paiement' => 'paye']);
            
            // Mettre à jour le solde du prestataire (montant - commission)
            if ($commande->prestataire_id) {
                $prestataire = \App\Models\Prestataire::find($commande->prestataire_id);
                if ($prestataire) {
                    $prestataire->increment('solde', $commande->montant_total - $commission);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Paiement cash enregistré',
                'data' => [
                    'id' => $transaction->id,
                    'commande_id' => $transaction->commande_id,
                    'montant' => $transaction->montant,
                    'commission' => $transaction->commission,
                    'type' => $transaction->type,
                    'methode_paiement' => $transaction->methode_paiement,
                    'statut' => $transaction->statut,
                    'reference' => $transaction->reference_externe,
                    'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                ],
            ], 201);
        }

        // Pour les paiements en ligne, initialiser avec le provider
        try {
            $paymentService = PaymentServiceFactory::make($request->provider);
            
            $paymentData = [
                'amount' => (float) $commande->montant_total,
                'currency' => 'XOF',
                'description' => 'Paiement Liggeyalma - Commande #' . $commande->id,
                'transaction_id' => $transaction->id,
                'commande_id' => $commande->id,
                'phone_number' => $request->phone_number ?? $user->telephone,
                'customer_name' => $user->name,
                'callback_url' => url('/api/payment/callback/' . $request->provider),
            ];

            $paymentResult = $paymentService->initializePayment($paymentData);

            if ($paymentResult['success']) {
                // Mettre à jour la transaction avec les infos du paiement
                $transaction->update([
                    'payment_id' => $paymentResult['payment_id'] ?? null,
                    'payment_url' => $paymentResult['payment_url'] ?? null,
                    'client_secret' => $paymentResult['client_secret'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Paiement initialisé avec succès',
                    'data' => [
                        'id' => $transaction->id,
                        'commande_id' => $transaction->commande_id,
                        'montant' => $transaction->montant,
                        'commission' => $transaction->commission,
                        'type' => $transaction->type,
                        'methode_paiement' => $transaction->methode_paiement,
                        'provider' => $transaction->provider,
                        'statut' => $transaction->statut,
                        'reference' => $transaction->reference_externe,
                        'payment_url' => $transaction->payment_url,
                        'payment_id' => $transaction->payment_id,
                        'client_secret' => $transaction->client_secret,
                        'public_key' => $paymentResult['public_key'] ?? null,
                        'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                    ],
                ], 201);
            } else {
                // En cas d'erreur, marquer la transaction comme refusée
                $transaction->update([
                    'statut' => 'refusee',
                    'notes' => $paymentResult['message'] ?? 'Erreur lors de l\'initialisation',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $paymentResult['message'] ?? 'Erreur lors de l\'initialisation du paiement',
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Payment Initialization Error: ' . $e->getMessage());
            
            $transaction->update([
                'statut' => 'refusee',
                'notes' => 'Erreur: ' . $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'initialisation du paiement: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify payment status
     */
    public function verify(Request $request, $id)
    {
        $user = $request->user();
        $transaction = Transaction::with('commande')->findOrFail($id);

        // Vérifier l'accès
        $client = \App\Models\Client::where('user_id', $user->id)->first();
        if (!$client || $transaction->commande->client_id != $client->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        // Si la transaction a un provider et un payment_id, vérifier le statut
        if ($transaction->provider && $transaction->payment_id) {
            try {
                $paymentService = PaymentServiceFactory::make($transaction->provider);
                $verificationResult = $paymentService->verifyPayment($transaction->payment_id);

                if ($verificationResult['success'] && $verificationResult['paid']) {
                    // Mettre à jour la transaction si elle est payée
                    if ($transaction->statut !== 'validee') {
                        $transaction->update(['statut' => 'validee']);
                        $transaction->commande->update(['statut_paiement' => 'paye']);

                        // Mettre à jour le solde du prestataire
                        if ($transaction->prestataire_id) {
                            $prestataire = \App\Models\Prestataire::find($transaction->prestataire_id);
                            if ($prestataire) {
                                $prestataire->increment('solde', $transaction->montant - $transaction->commission);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Payment Verification Error: ' . $e->getMessage());
            }
        }

        // Recharger la transaction
        $transaction->refresh();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $transaction->id,
                'statut' => $transaction->statut,
                'reference' => $transaction->reference_externe,
                'montant' => $transaction->montant,
                'commission' => $transaction->commission,
            ],
        ]);
    }

    /**
     * Handle payment callback from providers
     */
    public function callback(Request $request, string $provider)
    {
        try {
            $paymentService = PaymentServiceFactory::make($provider);
            
            // Handle callback based on provider
            $callbackData = $request->all();
            $result = $paymentService->handleCallback($callbackData);

            if ($result['success']) {
                // Find transaction by payment_id or metadata
                $transaction = null;
                
                if (isset($result['transaction_id'])) {
                    $transaction = Transaction::find($result['transaction_id']);
                } elseif (isset($callbackData['metadata']['transaction_id'])) {
                    $transaction = Transaction::find($callbackData['metadata']['transaction_id']);
                } else {
                    $paymentId = $result['payment_id'] ?? $result['reference_id'] ?? $result['order_id'] ?? null;
                    if ($paymentId) {
                        $transaction = Transaction::where('payment_id', $paymentId)->first();
                    }
                }

                if ($transaction && ($result['status'] === 'successful' || $result['status'] === 'succeeded' || $result['status'] === 'SUCCESS')) {
                    $transaction->update(['statut' => 'validee']);
                    $transaction->commande->update(['statut_paiement' => 'paye']);

                    // Mettre à jour le solde du prestataire
                    if ($transaction->prestataire_id) {
                        $prestataire = \App\Models\Prestataire::find($transaction->prestataire_id);
                        if ($prestataire) {
                            $prestataire->increment('solde', $transaction->montant - $transaction->commission);
                        }
                    }
                }
            }

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            Log::error('Payment Callback Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Download receipt as PDF
     */
    public function downloadReceipt(Request $request, $id)
    {
        $user = $request->user();
        $transaction = Transaction::with(['commande.client.user', 'commande.prestataire.user'])
            ->findOrFail($id);

        // Vérifier l'accès
        $userType = $user->role;
        $hasAccess = false;

        if ($userType === 'client') {
            $client = \App\Models\Client::where('user_id', $user->id)->first();
            $hasAccess = $client && $transaction->commande->client_id == $client->id;
        } elseif ($userType === 'prestataire') {
            $prestataire = \App\Models\Prestataire::where('user_id', $user->id)->first();
            $hasAccess = $prestataire && $transaction->commande->prestataire_id == $prestataire->id;
        }

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        // Vérifier que la transaction est validée
        if ($transaction->statut !== 'validee') {
            return response()->json([
                'success' => false,
                'message' => 'La transaction doit être validée pour générer un reçu',
            ], 400);
        }

        // Generate receipt data
        $receiptData = [
            'transaction_id' => $transaction->id,
            'reference' => $transaction->reference_externe,
            'date' => $transaction->created_at->format('d/m/Y H:i:s'),
            'montant' => number_format($transaction->montant, 0, ',', ' ') . ' FCFA',
            'commission' => number_format($transaction->commission, 0, ',', ' ') . ' FCFA',
            'montant_net' => number_format($transaction->montant - $transaction->commission, 0, ',', ' ') . ' FCFA',
            'methode_paiement' => $this->formatPaymentMethod($transaction->methode_paiement),
            'provider' => $this->formatProvider($transaction->provider),
            'commande' => [
                'id' => $transaction->commande->id,
                'description' => $transaction->commande->description,
                'date' => $transaction->commande->created_at->format('d/m/Y H:i'),
            ],
            'client' => [
                'name' => $transaction->commande->client->user->name ?? 'N/A',
                'email' => $transaction->commande->client->user->email ?? 'N/A',
                'telephone' => $transaction->commande->client->user->telephone ?? 'N/A',
            ],
            'prestataire' => [
                'name' => $transaction->commande->prestataire->user->name ?? 'N/A',
                'email' => $transaction->commande->prestataire->user->email ?? 'N/A',
                'telephone' => $transaction->commande->prestataire->user->telephone ?? 'N/A',
            ],
        ];

        // TODO: Generate PDF receipt using a library like dompdf or barryvdh/laravel-dompdf
        // For now, return receipt data as JSON
        // The mobile app can generate a PDF using a Flutter PDF library like pdf package
        
        return response()->json([
            'success' => true,
            'message' => 'Reçu généré avec succès',
            'data' => $receiptData,
            'pdf_url' => null, // Will be implemented when PDF generation is added
        ]);
    }

    /**
     * Format payment method for display
     */
    private function formatPaymentMethod(?string $method): string
    {
        return match ($method) {
            'cash' => 'Espèces',
            'mobile_money' => 'Mobile Money',
            'carte' => 'Carte bancaire',
            default => $method ?? 'N/A',
        };
    }

    /**
     * Format provider for display
     */
    private function formatProvider(?string $provider): string
    {
        return match ($provider) {
            'wave' => 'Wave',
            'orange_money' => 'Orange Money',
            'mtn' => 'MTN Mobile Money',
            'stripe' => 'Stripe',
            default => $provider ?? 'N/A',
        };
    }
}

