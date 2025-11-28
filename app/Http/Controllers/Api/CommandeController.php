<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Client;
use App\Services\Notifications\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CommandeController extends Controller
{
    /**
     * Get user's commandes
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $userType = $user->role;

        $query = Commande::with(['prestataire.user', 'client.user', 'categorieService', 'sousCategorieService']);

        if ($userType === 'client') {
            $client = Client::where('user_id', $user->id)->first();
            if ($client) {
                $query->where('client_id', $client->id);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Client non trouvé',
                ], 404);
            }
        } elseif ($userType === 'prestataire') {
            $prestataire = \App\Models\Prestataire::where('user_id', $user->id)->first();
            if ($prestataire) {
                $query->where('prestataire_id', $prestataire->id);
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

        // Filter by type
        if ($request->has('type')) {
            $query->where('type_commande', $request->type);
        }

        $perPage = $request->get('per_page', 15);
        $commandes = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $data = $commandes->map(function ($commande) use ($userType) {
            return $this->formatCommande($commande, $userType);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $commandes->currentPage(),
                'last_page' => $commandes->lastPage(),
                'per_page' => $commandes->perPage(),
                'total' => $commandes->total(),
            ],
        ]);
    }

    /**
     * Create a new commande
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'client') {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les clients peuvent créer des commandes',
            ], 403);
        }

        $client = Client::where('user_id', $user->id)->first();
        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'prestataire_id' => 'required|exists:prestataires,id',
            'categorie_service_id' => 'required|exists:categorie_services,id',
            'sous_categorie_service_id' => 'nullable|exists:sous_categorie_services,id',
            'type_commande' => 'required|in:immediate,programmee',
            'description' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'adresse_intervention' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'date_heure_souhaitee' => 'nullable|date',
            'montant_total' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Handle photos upload
        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photos[] = $photo->store('commandes/photos', 'public');
            }
        }

        $commande = Commande::create([
            'client_id' => $client->id,
            'prestataire_id' => $request->prestataire_id,
            'categorie_service_id' => $request->categorie_service_id,
            'sous_categorie_service_id' => $request->sous_categorie_service_id,
            'type_commande' => $request->type_commande,
            'description' => $request->description,
            'photos' => $photos,
            'adresse_intervention' => $request->adresse_intervention,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'date_heure_souhaitee' => $request->date_heure_souhaitee,
            'montant_total' => $request->montant_total ?? 0,
            'statut' => 'en_attente',
            'historique_statuts' => [
                [
                    'statut' => 'en_attente',
                    'date' => now()->toIso8601String(),
                ],
            ],
        ]);

        // Load relationships for notification
        $commande->load(['prestataire.user', 'client.user', 'categorieService', 'sousCategorieService']);

        // Send notification to prestataire about new order
        try {
            $fcmService = new FCMService();
            $prestataireUser = $commande->prestataire->user;
            if ($prestataireUser) {
                $fcmService->sendNewOrderNotification(
                    $prestataireUser->id,
                    [
                        'id' => $commande->id,
                        'categorie' => $commande->categorieService->nom ?? 'Service',
                    ]
                );
            }
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Error sending FCM notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Commande créée avec succès',
            'data' => $this->formatCommande($commande, 'client'),
        ], 201);
    }

    /**
     * Get commande details
     */
    public function show($id, Request $request)
    {
        $user = $request->user();
        $commande = Commande::with(['prestataire.user', 'client.user', 'categorieService', 'sousCategorieService', 'avis', 'transaction'])
            ->findOrFail($id);

        // Check if user has access to this commande
        if ($user->role === 'client') {
            $client = Client::where('user_id', $user->id)->first();
            if ($commande->client_id !== $client->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé',
                ], 403);
            }
        } elseif ($user->role === 'prestataire') {
            $prestataire = \App\Models\Prestataire::where('user_id', $user->id)->first();
            if ($commande->prestataire_id !== $prestataire->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé',
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatCommande($commande, $user->role),
        ]);
    }

    /**
     * Update commande status
     */
    public function updateStatus($id, Request $request)
    {
        $user = $request->user();
        $commande = Commande::findOrFail($id);

        // Check permissions
        if ($user->role === 'prestataire') {
            $prestataire = \App\Models\Prestataire::where('user_id', $user->id)->first();
            if ($commande->prestataire_id !== $prestataire->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé',
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'statut' => 'required|in:acceptee,refusee,en_route,arrivee,en_cours,terminee,annulee',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        $oldStatus = $commande->statut;
        $newStatus = $request->statut;

        // Update historique
        $historique = $commande->historique_statuts ?? [];
        $historique[] = [
            'statut' => $newStatus,
            'date' => now()->toIso8601String(),
            'changed_by' => $user->role,
        ];

        $commande->update([
            'statut' => $newStatus,
            'historique_statuts' => $historique,
        ]);

        // Load relationships
        $commande->load(['prestataire.user', 'client.user', 'categorieService', 'sousCategorieService']);

        // Send notification about status change
        try {
            $fcmService = new FCMService();
            // Notify client about status change
            if ($commande->client && $commande->client->user) {
                $fcmService->sendOrderStatusNotification(
                    $commande->client->user->id,
                    $newStatus,
                    ['id' => $commande->id]
                );
            }
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Error sending FCM notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès',
            'data' => $this->formatCommande($commande, $user->role),
        ]);
    }

    /**
     * Format commande for API response
     */
    private function formatCommande($commande, $userType)
    {
        $prestataire = $commande->prestataire;
        $client = $commande->client;
        $photos = [];
        
        if ($commande->photos) {
            foreach ($commande->photos as $photo) {
                $photos[] = asset('storage/' . $photo);
            }
        }

        $data = [
            'id' => $commande->id,
            'statut' => $commande->statut,
            'type_commande' => $commande->type_commande,
            'description' => $commande->description,
            'photos' => $photos,
            'adresse_intervention' => $commande->adresse_intervention,
            'latitude' => $commande->latitude ? (float) $commande->latitude : null,
            'longitude' => $commande->longitude ? (float) $commande->longitude : null,
            'date_heure_souhaitee' => $commande->date_heure_souhaitee?->toIso8601String(),
            'montant_total' => $commande->montant_total ? (float) $commande->montant_total : null,
            'methode_paiement' => $commande->methode_paiement,
            'statut_paiement' => $commande->statut_paiement,
            'historique_statuts' => $commande->historique_statuts ?? [],
            'created_at' => $commande->created_at->toIso8601String(),
            'updated_at' => $commande->updated_at->toIso8601String(),
        ];

        // Add categorie info
        if ($commande->categorieService) {
            $data['categorie'] = [
                'id' => $commande->categorieService->id,
                'nom' => $commande->categorieService->nom,
            ];
        }

        if ($commande->sousCategorieService) {
            $data['sous_categorie'] = [
                'id' => $commande->sousCategorieService->id,
                'nom' => $commande->sousCategorieService->nom,
            ];
        }

        // Add prestataire info (for clients)
        if ($userType === 'client' && $prestataire && $prestataire->user) {
            $data['prestataire'] = [
                'id' => $prestataire->id,
                'name' => $prestataire->user->name,
                'photo' => $prestataire->user->photo ? asset('storage/' . $prestataire->user->photo) : null,
                'metier' => $prestataire->metier,
                'phone' => $prestataire->user->phone,
            ];
        }

        // Add client info (for prestataires)
        if ($userType === 'prestataire' && $client && $client->user) {
            $data['client'] = [
                'id' => $client->id,
                'name' => $client->user->name,
                'photo' => $client->user->photo ? asset('storage/' . $client->user->photo) : null,
                'phone' => $client->user->phone,
            ];
        }

        return $data;
    }
}

