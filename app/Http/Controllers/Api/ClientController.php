<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Prestataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    /**
     * Get client's favorite addresses
     */
    public function getFavoriteAddresses(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'client') {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $client = Client::where('user_id', $user->id)->first();
        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé',
            ], 404);
        }

        $addresses = $client->adresses_favorites ?? [];

        return response()->json([
            'success' => true,
            'data' => $addresses,
        ]);
    }

    /**
     * Add favorite address
     */
    public function addFavoriteAddress(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'client') {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        $client = Client::where('user_id', $user->id)->first();
        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé',
            ], 404);
        }

        $addresses = $client->adresses_favorites ?? [];
        
        $newAddress = [
            'id' => uniqid(),
            'label' => $request->label,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'created_at' => now()->toIso8601String(),
        ];

        $addresses[] = $newAddress;
        $client->update(['adresses_favorites' => $addresses]);

        return response()->json([
            'success' => true,
            'message' => 'Adresse ajoutée aux favoris',
            'data' => $newAddress,
        ]);
    }

    /**
     * Update favorite address
     */
    public function updateFavoriteAddress(Request $request, $addressId)
    {
        $user = $request->user();
        
        if ($user->role !== 'client') {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        $client = Client::where('user_id', $user->id)->first();
        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé',
            ], 404);
        }

        $addresses = $client->adresses_favorites ?? [];
        $index = array_search($addressId, array_column($addresses, 'id'));

        if ($index === false) {
            return response()->json([
                'success' => false,
                'message' => 'Adresse non trouvée',
            ], 404);
        }

        $addresses[$index] = [
            'id' => $addressId,
            'label' => $request->label,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'created_at' => $addresses[$index]['created_at'] ?? now()->toIso8601String(),
        ];

        $client->update(['adresses_favorites' => $addresses]);

        return response()->json([
            'success' => true,
            'message' => 'Adresse mise à jour',
            'data' => $addresses[$index],
        ]);
    }

    /**
     * Delete favorite address
     */
    public function deleteFavoriteAddress(Request $request, $addressId)
    {
        $user = $request->user();
        
        if ($user->role !== 'client') {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $client = Client::where('user_id', $user->id)->first();
        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé',
            ], 404);
        }

        $addresses = $client->adresses_favorites ?? [];
        $filteredAddresses = array_filter($addresses, function ($addr) use ($addressId) {
            return ($addr['id'] ?? '') !== $addressId;
        });

        $client->update(['adresses_favorites' => array_values($filteredAddresses)]);

        return response()->json([
            'success' => true,
            'message' => 'Adresse supprimée des favoris',
        ]);
    }

    /**
     * Get favorite prestataires
     */
    public function getFavoritePrestataires(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'client') {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $client = Client::where('user_id', $user->id)->first();
        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé',
            ], 404);
        }

        $favorites = DB::table('client_favoris_prestataires')
            ->where('client_id', $client->id)
            ->join('prestataires', 'client_favoris_prestataires.prestataire_id', '=', 'prestataires.id')
            ->join('users', 'prestataires.user_id', '=', 'users.id')
            ->select(
                'prestataires.id',
                'prestataires.metier',
                'prestataires.description',
                'prestataires.tarif_horaire',
                'prestataires.latitude',
                'prestataires.longitude',
                'prestataires.disponible',
                'users.name',
                'users.photo',
                'users.phone',
                'client_favoris_prestataires.created_at as favorited_at'
            )
            ->get();

        // Calculate ratings for each prestataire
        $data = $favorites->map(function ($fav) {
            $prestataireId = $fav->id;
            
            $avgRating = DB::table('avis')
                ->where('prestataire_id', $prestataireId)
                ->avg('note');
            
            $totalAvis = DB::table('avis')
                ->where('prestataire_id', $prestataireId)
                ->count();

            return [
                'id' => $prestataireId,
                'name' => $fav->name,
                'photo' => $fav->photo ? asset('storage/' . $fav->photo) : null,
                'metier' => $fav->metier,
                'description' => $fav->description,
                'tarif_horaire' => (float) $fav->tarif_horaire,
                'latitude' => $fav->latitude ? (float) $fav->latitude : null,
                'longitude' => $fav->longitude ? (float) $fav->longitude : null,
                'disponible' => (bool) $fav->disponible,
                'note_moyenne' => round($avgRating ?? 0, 2),
                'nombre_avis' => $totalAvis,
                'favorited_at' => $fav->favorited_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Add prestataire to favorites
     */
    public function addFavoritePrestataire(Request $request, $prestataireId)
    {
        $user = $request->user();
        
        if ($user->role !== 'client') {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $client = Client::where('user_id', $user->id)->first();
        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé',
            ], 404);
        }

        $prestataire = Prestataire::find($prestataireId);
        if (!$prestataire) {
            return response()->json([
                'success' => false,
                'message' => 'Prestataire non trouvé',
            ], 404);
        }

        // Check if already favorited
        $exists = DB::table('client_favoris_prestataires')
            ->where('client_id', $client->id)
            ->where('prestataire_id', $prestataireId)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Prestataire déjà dans les favoris',
            ], 400);
        }

        DB::table('client_favoris_prestataires')->insert([
            'client_id' => $client->id,
            'prestataire_id' => $prestataireId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Prestataire ajouté aux favoris',
        ]);
    }

    /**
     * Remove prestataire from favorites
     */
    public function removeFavoritePrestataire(Request $request, $prestataireId)
    {
        $user = $request->user();
        
        if ($user->role !== 'client') {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $client = Client::where('user_id', $user->id)->first();
        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé',
            ], 404);
        }

        DB::table('client_favoris_prestataires')
            ->where('client_id', $client->id)
            ->where('prestataire_id', $prestataireId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Prestataire retiré des favoris',
        ]);
    }
}

