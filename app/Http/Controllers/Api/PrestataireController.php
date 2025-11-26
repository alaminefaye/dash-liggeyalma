<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prestataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrestataireController extends Controller
{
    /**
     * Search prestataires with filters
     */
    public function search(Request $request)
    {
        $query = Prestataire::with(['user', 'avis'])
            ->where('statut_inscription', 'valide')
            ->where('disponible', true);

        // Filter by category
        if ($request->has('categorie_id')) {
            $query->whereHas('commandes', function ($q) use ($request) {
                $q->where('categorie_service_id', $request->categorie_id);
            });
        }

        // Filter by keyword (name, metier)
        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('metier', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        // Filter by minimum rating
        if ($request->has('min_rating')) {
            $minRating = $request->min_rating;
            $query->whereHas('avis', function ($q) use ($minRating) {
                $q->select('prestataire_id', DB::raw('AVG(note) as avg_note'))
                    ->groupBy('prestataire_id')
                    ->having('avg_note', '>=', $minRating);
            });
        }

        // Filter by distance (if latitude and longitude provided)
        if ($request->has('latitude') && $request->has('longitude')) {
            $lat = $request->latitude;
            $lng = $request->longitude;
            $maxDistance = $request->get('max_distance', 50); // Default 50km

            $query->selectRaw("*, (
                6371 * acos(
                    cos(radians(?))
                    * cos(radians(latitude))
                    * cos(radians(longitude) - radians(?))
                    + sin(radians(?))
                    * sin(radians(latitude))
                )
            ) AS distance", [$lat, $lng, $lat])
                ->having('distance', '<=', $maxDistance)
                ->orderBy('distance');
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('tarif_horaire', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('tarif_horaire', '<=', $request->max_price);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'distance'); // distance, rating, price
        switch ($sortBy) {
            case 'rating':
                $query->withAvg('avis', 'note')->orderBy('avis_avg_note', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('tarif_horaire', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('tarif_horaire', 'desc');
                break;
            default:
                // Already sorted by distance if coordinates provided
                if (!$request->has('latitude')) {
                    $query->orderBy('score_confiance', 'desc');
                }
        }

        $perPage = $request->get('per_page', 15);
        $prestataires = $query->paginate($perPage);

        $data = $prestataires->map(function ($prestataire) use ($request) {
            $user = $prestataire->user;
            $avgRating = $prestataire->avis()->avg('note') ?? 0;
            $totalAvis = $prestataire->avis()->count();

            $result = [
                'id' => $prestataire->id,
                'user_id' => $prestataire->user_id,
                'name' => $user->name,
                'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                'metier' => $prestataire->metier,
                'specialites' => $prestataire->specialites ?? [],
                'description' => $prestataire->description,
                'annees_experience' => $prestataire->annees_experience,
                'tarif_horaire' => (float) $prestataire->tarif_horaire,
                'frais_deplacement' => (float) $prestataire->frais_deplacement,
                'note_moyenne' => round($avgRating, 2),
                'nombre_avis' => $totalAvis,
                'disponible' => $prestataire->disponible,
                'score_confiance' => (float) $prestataire->score_confiance,
            ];

            if ($request->has('latitude') && isset($prestataire->distance)) {
                $result['distance'] = round($prestataire->distance, 2);
            }

            return $result;
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $prestataires->currentPage(),
                'last_page' => $prestataires->lastPage(),
                'per_page' => $prestataires->perPage(),
                'total' => $prestataires->total(),
            ],
        ]);
    }

    /**
     * Get all prestataires (simplified)
     */
    public function index(Request $request)
    {
        return $this->search($request);
    }

    /**
     * Get prestataire details
     */
    public function show($id)
    {
        $prestataire = Prestataire::with(['user', 'avis.client.user'])
            ->where('statut_inscription', 'valide')
            ->findOrFail($id);

        $user = $prestataire->user;
        $avgRating = $prestataire->avis()->avg('note') ?? 0;
        $totalAvis = $prestataire->avis()->count();
        $totalInterventions = $prestataire->commandes()->where('statut', 'terminee')->count();

        // Get recent avis
        $recentAvis = $prestataire->avis()
            ->with('client.user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($avis) {
                $client = $avis->client->user ?? null;
                return [
                    'id' => $avis->id,
                    'note' => $avis->note,
                    'commentaire' => $avis->commentaire,
                    'photos' => $avis->photos ?? [],
                    'client_name' => $client->name ?? 'Anonyme',
                    'client_photo' => $client->photo ? asset('storage/' . $client->photo) : null,
                    'date' => $avis->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $prestataire->id,
                'user_id' => $prestataire->user_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                'metier' => $prestataire->metier,
                'specialites' => $prestataire->specialites ?? [],
                'description' => $prestataire->description,
                'annees_experience' => $prestataire->annees_experience,
                'tarif_horaire' => (float) $prestataire->tarif_horaire,
                'forfaits' => $prestataire->forfaits ?? [],
                'frais_deplacement' => (float) $prestataire->frais_deplacement,
                'zone_intervention' => $prestataire->zone_intervention ?? [],
                'latitude' => $prestataire->latitude ? (float) $prestataire->latitude : null,
                'longitude' => $prestataire->longitude ? (float) $prestataire->longitude : null,
                'rayon_intervention' => $prestataire->rayon_intervention,
                'note_moyenne' => round($avgRating, 2),
                'nombre_avis' => $totalAvis,
                'nombre_interventions' => $totalInterventions,
                'disponible' => $prestataire->disponible,
                'score_confiance' => (float) $prestataire->score_confiance,
                'avis' => $recentAvis,
            ],
        ]);
    }
}

