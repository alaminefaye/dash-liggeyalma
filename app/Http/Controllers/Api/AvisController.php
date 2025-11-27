<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Avis;
use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AvisController extends Controller
{
    /**
     * Get avis for a prestataire
     */
    public function index(Request $request, $prestataireId = null)
    {
        $query = Avis::with(['client.user', 'commande']);

        if ($prestataireId) {
            $query->where('prestataire_id', $prestataireId);
        }

        // Filter by note
        if ($request->has('note')) {
            $query->where('note', $request->note);
        }

        $perPage = $request->get('per_page', 15);
        $avis = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $avis->items(),
            'pagination' => [
                'current_page' => $avis->currentPage(),
                'last_page' => $avis->lastPage(),
                'per_page' => $avis->perPage(),
                'total' => $avis->total(),
            ],
        ]);
    }

    /**
     * Create a new avis
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'commande_id' => 'required|exists:commandes,id',
            'note' => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:1000',
            'criteres' => 'nullable|array',
            'criteres.ponctualite' => 'nullable|integer|min:1|max:5',
            'criteres.qualite' => 'nullable|integer|min:1|max:5',
            'criteres.professionnalisme' => 'nullable|integer|min:1|max:5',
            'criteres.rapport_qualite_prix' => 'nullable|integer|min:1|max:5',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $commande = Commande::with('client')->findOrFail($request->commande_id);

        // Vérifier que l'utilisateur est le client de la commande
        $client = \App\Models\Client::where('user_id', $user->id)->first();
        if (!$client || $commande->client_id != $client->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        // Vérifier que la commande est terminée
        if ($commande->statut !== 'terminee' && $commande->statut !== 'terminée') {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez laisser un avis que pour une commande terminée',
            ], 400);
        }

        // Vérifier qu'il n'y a pas déjà un avis pour cette commande
        $existingAvis = Avis::where('commande_id', $commande->id)->first();
        if ($existingAvis) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà laissé un avis pour cette commande',
            ], 400);
        }

        // Traiter les photos
        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('avis', 'public');
                $photos[] = $path;
            }
        }

        // Créer l'avis
        $avis = Avis::create([
            'commande_id' => $commande->id,
            'client_id' => $client->id,
            'prestataire_id' => $commande->prestataire_id,
            'note' => $request->note,
            'commentaire' => $request->commentaire,
            'criteres' => $request->criteres,
            'photos' => !empty($photos) ? $photos : null,
        ]);

        // Note: La note moyenne et le nombre d'avis sont calculés à la volée
        // dans PrestataireController lors de la récupération des données

        return response()->json([
            'success' => true,
            'message' => 'Avis créé avec succès',
            'data' => [
                'id' => $avis->id,
                'commande_id' => $avis->commande_id,
                'prestataire_id' => $avis->prestataire_id,
                'note' => $avis->note,
                'commentaire' => $avis->commentaire,
                'criteres' => $avis->criteres,
                'photos' => $avis->photos,
                'created_at' => $avis->created_at->format('Y-m-d H:i:s'),
            ],
        ], 201);
    }

    /**
     * Get avis details
     */
    public function show($id)
    {
        $avis = Avis::with(['client.user', 'prestataire.user', 'commande'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $avis->id,
                'commande_id' => $avis->commande_id,
                'prestataire_id' => $avis->prestataire_id,
                'note' => $avis->note,
                'commentaire' => $avis->commentaire,
                'criteres' => $avis->criteres,
                'photos' => $avis->photos,
                'reponse_prestataire' => $avis->reponse_prestataire,
                'date_reponse' => $avis->date_reponse?->format('Y-m-d H:i:s'),
                'client' => [
                    'id' => $avis->client->id,
                    'name' => $avis->client->user->name,
                    'photo' => $avis->client->user->photo,
                ],
                'created_at' => $avis->created_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}

