<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Client;
use App\Models\Prestataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string', // email or phone
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Find user by email or phone
        $user = User::where('email', $request->identifier)
            ->orWhere('phone', $request->identifier)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants incorrects',
            ], 401);
        }

        // Check if user is active
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte est suspendu ou bloqué',
            ], 403);
        }

        // Create token
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                    'status' => $user->status,
                    'type' => $user->role, // Return role as 'type' for mobile compatibility
                    'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                    'created_at' => $user->created_at->toIso8601String(),
                    'updated_at' => $user->updated_at->toIso8601String(),
                ],
            ],
        ]);
    }

    /**
     * Register new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'identifier' => 'required|string', // email or phone
            'password' => 'required|string|min:6|confirmed',
            'type' => 'nullable|string|in:client,prestataire', // Type d'utilisateur
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Determine if identifier is email or phone
        $isEmail = filter_var($request->identifier, FILTER_VALIDATE_EMAIL);
        
        // Check if user already exists
        if ($isEmail) {
            $existingUser = User::where('email', $request->identifier)->first();
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet email est déjà utilisé',
                ], 409);
            }
        } else {
            $existingUser = User::where('phone', $request->identifier)->first();
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce numéro de téléphone est déjà utilisé',
                ], 409);
            }
        }

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
        }

        // Get user type (default: client)
        $userType = $request->input('type', 'client');

        // Use transaction to ensure data consistency
        try {
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $isEmail ? $request->identifier : null,
                'phone' => !$isEmail ? $request->identifier : null,
                'password' => Hash::make($request->password),
                'role' => $userType, // client or prestataire
                'photo' => $photoPath,
                'status' => 'active',
            ]);

            // Create corresponding Client or Prestataire record
            if ($userType === 'prestataire') {
                Prestataire::create([
                    'user_id' => $user->id,
                    'statut_inscription' => 'en_attente', // En attente de validation admin
                    'solde' => 0,
                    'score_confiance' => 0,
                    'disponible' => false,
                ]);
            } else {
                Client::create([
                    'user_id' => $user->id,
                    'score_confiance' => 0,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du compte: ' . $e->getMessage(),
            ], 500);
        }

        // Create token
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Inscription réussie',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                    'status' => $user->status,
                    'type' => $user->role, // Return role as 'type' for mobile compatibility
                    'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                    'created_at' => $user->created_at->toIso8601String(),
                    'updated_at' => $user->updated_at->toIso8601String(),
                ],
            ],
        ], 201);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie',
        ]);
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                'status' => $user->status,
                'type' => $user->role,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'created_at' => $user->created_at->toIso8601String(),
                'updated_at' => $user->updated_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|unique:users,phone,' . $user->id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->only(['name', 'email', 'phone']);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo) {
                \Storage::disk('public')->delete($user->photo);
            }
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                'status' => $user->status,
                'type' => $user->role,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'created_at' => $user->created_at->toIso8601String(),
                'updated_at' => $user->updated_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Upload user photo
     */
    public function uploadPhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Delete old photo if exists
        if ($user->photo) {
            \Storage::disk('public')->delete($user->photo);
        }

        $photoPath = $request->file('photo')->store('photos', 'public');
        $user->update(['photo' => $photoPath]);

        return response()->json([
            'success' => true,
            'message' => 'Photo mise à jour avec succès',
            'data' => [
                'photo' => asset('storage/' . $photoPath),
            ],
        ]);
    }

    /**
     * Send password reset code
     */
    public function sendPasswordResetCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string', // email or phone
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Find user by email or phone
        $user = User::where('email', $request->identifier)
            ->orWhere('phone', $request->identifier)
            ->first();

        if (!$user) {
            // Don't reveal if user exists for security
            return response()->json([
                'success' => true,
                'message' => 'Si ce compte existe, un code de réinitialisation a été envoyé',
            ]);
        }

        // Generate OTP code (6 digits)
        $otp = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in cache for 10 minutes
        \Cache::put('password_reset_' . $user->id, $otp, now()->addMinutes(10));

        // TODO: Send OTP via SMS or Email
        // For now, we'll just return success
        // In production, implement SMS/Email sending here

        return response()->json([
            'success' => true,
            'message' => 'Code de réinitialisation envoyé',
            'data' => [
                'user_id' => $user->id,
                // In development, you might want to return the OTP for testing
                // 'otp' => $otp, // Remove in production
            ],
        ]);
    }

    /**
     * Reset password with OTP
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Find user
        $user = User::where('email', $request->identifier)
            ->orWhere('phone', $request->identifier)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé',
            ], 404);
        }

        // Verify OTP
        $storedOtp = \Cache::get('password_reset_' . $user->id);
        if (!$storedOtp || $storedOtp !== $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Code OTP invalide ou expiré',
            ], 400);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Delete OTP from cache
        \Cache::forget('password_reset_' . $user->id);

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès',
        ]);
    }
}

