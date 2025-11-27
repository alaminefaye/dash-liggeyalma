<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Register FCM token
     */
    public function registerToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string',
            'device_type' => 'nullable|string|in:ios,android',
            'device_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        try {
            // Check if token already exists for this user
            $existingToken = DB::table('fcm_tokens')
                ->where('user_id', $user->id)
                ->where('fcm_token', $request->fcm_token)
                ->first();

            if ($existingToken) {
                // Update existing token
                DB::table('fcm_tokens')
                    ->where('id', $existingToken->id)
                    ->update([
                        'is_active' => true,
                        'device_type' => $request->device_type,
                        'device_id' => $request->device_id,
                        'updated_at' => now(),
                    ]);
            } else {
                // Insert new token
                DB::table('fcm_tokens')->insert([
                    'user_id' => $user->id,
                    'fcm_token' => $request->fcm_token,
                    'device_type' => $request->device_type,
                    'device_id' => $request->device_id,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'FCM token registered successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error registering token: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unregister FCM token
     */
    public function unregisterToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        try {
            DB::table('fcm_tokens')
                ->where('user_id', $user->id)
                ->where('fcm_token', $request->fcm_token)
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token unregistered successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error unregistering token: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's notification preferences
     */
    public function getPreferences(Request $request)
    {
        $user = $request->user();

        // Get or create user preferences
        $preferences = \DB::table('user_preferences')
            ->where('user_id', $user->id)
            ->first();

        if (!$preferences) {
            // Create default preferences
            \DB::table('user_preferences')->insert([
                'user_id' => $user->id,
                'new_request' => true,
                'order_status' => true,
                'new_message' => true,
                'payment_received' => true,
                'review_received' => true,
                'language' => 'fr',
                'dark_mode' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $preferences = \DB::table('user_preferences')
                ->where('user_id', $user->id)
                ->first();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'new_request' => (bool) $preferences->new_request,
                'order_status' => (bool) $preferences->order_status,
                'new_message' => (bool) $preferences->new_message,
                'payment_received' => (bool) $preferences->payment_received,
                'review_received' => (bool) $preferences->review_received,
                'language' => $preferences->language,
                'dark_mode' => (bool) $preferences->dark_mode,
            ],
        ]);
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_request' => 'boolean',
            'order_status' => 'boolean',
            'new_message' => 'boolean',
            'payment_received' => 'boolean',
            'review_received' => 'boolean',
            'language' => 'string|in:fr,en',
            'dark_mode' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        
        // Prepare update data
        $updateData = [
            'updated_at' => now(),
        ];
        
        if ($request->has('new_request')) {
            $updateData['new_request'] = $request->new_request;
        }
        if ($request->has('order_status')) {
            $updateData['order_status'] = $request->order_status;
        }
        if ($request->has('new_message')) {
            $updateData['new_message'] = $request->new_message;
        }
        if ($request->has('payment_received')) {
            $updateData['payment_received'] = $request->payment_received;
        }
        if ($request->has('review_received')) {
            $updateData['review_received'] = $request->review_received;
        }
        if ($request->has('language')) {
            $updateData['language'] = $request->language;
        }
        if ($request->has('dark_mode')) {
            $updateData['dark_mode'] = $request->dark_mode;
        }

        // Update or create preferences
        $exists = \DB::table('user_preferences')
            ->where('user_id', $user->id)
            ->exists();

        if ($exists) {
            \DB::table('user_preferences')
                ->where('user_id', $user->id)
                ->update($updateData);
        } else {
            $updateData['user_id'] = $user->id;
            $updateData['created_at'] = now();
            \DB::table('user_preferences')->insert($updateData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully',
        ]);
    }
}

