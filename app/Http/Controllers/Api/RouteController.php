<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class RouteController extends Controller
{
    /**
     * Get route between two points using Google Directions API
     */
    public function getRoute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin_lat' => 'required|numeric|between:-90,90',
            'origin_lng' => 'required|numeric|between:-180,180',
            'destination_lat' => 'required|numeric|between:-90,90',
            'destination_lng' => 'required|numeric|between:-180,180',
            'mode' => 'nullable|string|in:driving,walking,bicycling,transit',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        $originLat = $request->origin_lat;
        $originLng = $request->origin_lng;
        $destLat = $request->destination_lat;
        $destLng = $request->destination_lng;
        $mode = $request->mode ?? 'driving';

        try {
            // Get Google Maps API key from config
            $apiKey = config('services.google_maps.api_key');
            
            if (!$apiKey) {
                // Fallback: Calculate simple route without Google API
                return $this->getSimpleRoute($originLat, $originLng, $destLat, $destLng, $mode);
            }

            // Call Google Directions API
            $response = Http::get('https://maps.googleapis.com/maps/api/directions/json', [
                'origin' => "$originLat,$originLng",
                'destination' => "$destLat,$destLng",
                'mode' => $mode,
                'key' => $apiKey,
                'language' => 'fr',
                'units' => 'metric',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'OK' && !empty($data['routes'])) {
                    $route = $data['routes'][0];
                    $leg = $route['legs'][0];
                    
                    // Extract polyline points
                    $polyline = $route['overview_polyline']['points'] ?? '';
                    
                    // Extract detailed points
                    $points = [];
                    foreach ($route['legs'] as $leg) {
                        foreach ($leg['steps'] as $step) {
                            $startLocation = $step['start_location'];
                            $points[] = [
                                'lat' => $startLocation['lat'],
                                'lng' => $startLocation['lng'],
                            ];
                        }
                        // Add end location of last step
                        $endLocation = $leg['end_location'];
                        $points[] = [
                            'lat' => $endLocation['lat'],
                            'lng' => $endLocation['lng'],
                        ];
                    }

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'distance' => $leg['distance']['value'], // in meters
                            'duration' => $leg['duration']['value'], // in seconds
                            'distance_text' => $leg['distance']['text'],
                            'duration_text' => $leg['duration']['text'],
                            'points' => $points,
                            'polyline' => $polyline,
                            'start_address' => $leg['start_address'],
                            'end_address' => $leg['end_address'],
                        ],
                    ]);
                } else {
                    // Fallback to simple route if Google API fails
                    return $this->getSimpleRoute($originLat, $originLng, $destLat, $destLng, $mode);
                }
            } else {
                // Fallback to simple route if API call fails
                return $this->getSimpleRoute($originLat, $originLng, $destLat, $destLng, $mode);
            }
        } catch (\Exception $e) {
            // Fallback to simple route on error
            return $this->getSimpleRoute($originLat, $originLng, $destLat, $destLng, $mode);
        }
    }

    /**
     * Calculate simple route without Google API (fallback)
     */
    private function getSimpleRoute($originLat, $originLng, $destLat, $destLng, $mode)
    {
        // Calculate distance using Haversine formula
        $distance = $this->haversineDistance($originLat, $originLng, $destLat, $destLng);
        
        // Estimate duration based on mode
        $speed = match($mode) {
            'walking' => 1.4, // m/s (~5 km/h)
            'bicycling' => 4.2, // m/s (~15 km/h)
            'transit' => 2.8, // m/s (~10 km/h)
            default => 11.1, // m/s (~40 km/h for driving in city)
        };
        
        $duration = (int) ($distance / $speed);

        return response()->json([
            'success' => true,
            'data' => [
                'distance' => $distance,
                'duration' => $duration,
                'distance_text' => $this->formatDistance($distance),
                'duration_text' => $this->formatDuration($duration),
                'points' => [
                    ['lat' => $originLat, 'lng' => $originLng],
                    ['lat' => $destLat, 'lng' => $destLng],
                ],
                'polyline' => '',
                'start_address' => null,
                'end_address' => null,
            ],
        ]);
    }

    /**
     * Calculate distance using Haversine formula (in meters)
     */
    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    /**
     * Format distance for display
     */
    private function formatDistance($distanceInMeters)
    {
        if ($distanceInMeters < 1000) {
            return round($distanceInMeters) . ' m';
        } else {
            return number_format($distanceInMeters / 1000, 1) . ' km';
        }
    }

    /**
     * Format duration for display
     */
    private function formatDuration($durationInSeconds)
    {
        if ($durationInSeconds < 60) {
            return $durationInSeconds . ' sec';
        } elseif ($durationInSeconds < 3600) {
            $minutes = round($durationInSeconds / 60);
            return $minutes . ' min';
        } else {
            $hours = floor($durationInSeconds / 3600);
            $minutes = round(($durationInSeconds % 3600) / 60);
            if ($minutes == 0) {
                return $hours . ' h';
            }
            return $hours . ' h ' . $minutes . ' min';
        }
    }
}


