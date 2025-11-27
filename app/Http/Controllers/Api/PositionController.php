<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Prestataire;
use App\Models\Commande;

class PositionController extends Controller
{
    /**
     * Update prestataire's current position
     */
    public function updatePosition(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'prestataire') {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les prestataires peuvent mettre à jour leur position',
            ], 403);
        }

        $prestataire = Prestataire::where('user_id', $user->id)->first();
        if (!$prestataire) {
            return response()->json([
                'success' => false,
                'message' => 'Prestataire non trouvé',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'commande_id' => 'nullable|exists:commandes,id',
            'accuracy' => 'nullable|numeric|min:0',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Update prestataire's base location
            $prestataire->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            // Store position history
            DB::table('prestataire_positions')->insert([
                'prestataire_id' => $prestataire->id,
                'commande_id' => $request->commande_id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy' => $request->accuracy,
                'speed' => $request->speed,
                'heading' => $request->heading,
                'timestamp' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Position mise à jour avec succès',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get prestataire's current position for a commande
     */
    public function getPosition(Request $request, $commandeId)
    {
        $user = $request->user();
        
        $commande = Commande::with('prestataire')->find($commandeId);
        if (!$commande) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée',
            ], 404);
        }

        // Check access: only client or the prestataire assigned to this commande
        if ($user->role === 'client') {
            $client = \App\Models\Client::where('user_id', $user->id)->first();
            if (!$client || $commande->client_id !== $client->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé',
                ], 403);
            }
        } elseif ($user->role === 'prestataire') {
            $prestataire = Prestataire::where('user_id', $user->id)->first();
            if (!$prestataire || $commande->prestataire_id !== $prestataire->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé',
                ], 403);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        // Get latest position for this commande
        $latestPosition = DB::table('prestataire_positions')
            ->where('commande_id', $commandeId)
            ->where('prestataire_id', $commande->prestataire_id)
            ->orderBy('timestamp', 'desc')
            ->first();

        // Fallback to prestataire's base location if no position history
        if (!$latestPosition && $commande->prestataire) {
            $prestataire = $commande->prestataire;
            if ($prestataire->latitude && $prestataire->longitude) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'latitude' => (float) $prestataire->latitude,
                        'longitude' => (float) $prestataire->longitude,
                        'accuracy' => null,
                        'speed' => null,
                        'heading' => null,
                        'timestamp' => $prestataire->updated_at->toIso8601String(),
                    ],
                ]);
            }
        }

        if (!$latestPosition) {
            return response()->json([
                'success' => false,
                'message' => 'Position non disponible',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'latitude' => (float) $latestPosition->latitude,
                'longitude' => (float) $latestPosition->longitude,
                'accuracy' => $latestPosition->accuracy ? (float) $latestPosition->accuracy : null,
                'speed' => $latestPosition->speed ? (float) $latestPosition->speed : null,
                'heading' => $latestPosition->heading ? (float) $latestPosition->heading : null,
                'timestamp' => $latestPosition->timestamp,
            ],
        ]);
    }
}

