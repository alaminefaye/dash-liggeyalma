<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategorieController;
use App\Http\Controllers\Api\PrestataireController;
use App\Http\Controllers\Api\CommandeController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\AvisController;
use App\Http\Controllers\Api\RetraitController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PositionController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\RouteController;

/*r
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/send-password-reset-code', [AuthController::class, 'sendPasswordResetCode']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Public categories (no auth needed)
Route::get('/categories', [CategorieController::class, 'index']);
Route::get('/categories/{id}', [CategorieController::class, 'show']);
Route::get('/sous-categories', [CategorieController::class, 'sousCategories']);
Route::get('/categories/{id}/sous-categories', [CategorieController::class, 'sousCategoriesByCategorie']);

// Public prestataires search (no auth needed for browsing)
Route::get('/prestataires', [PrestataireController::class, 'index']);
Route::get('/prestataires/search', [PrestataireController::class, 'search']);
Route::get('/prestataires/{id}', [PrestataireController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::prefix('user')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/photo', [AuthController::class, 'uploadPhoto']);
    });
    
    // Prestataire routes
    Route::prefix('prestataire')->group(function () {
        Route::put('/availability', [PrestataireController::class, 'updateAvailability']);
        Route::get('/dashboard-stats', [PrestataireController::class, 'dashboardStats']);
        Route::get('/gallery', [PrestataireController::class, 'getGallery']);
        Route::post('/gallery', [PrestataireController::class, 'addPhotoToGallery']);
        Route::delete('/gallery/{photoIndex}', [PrestataireController::class, 'deletePhotoFromGallery']);
    });
    
    // Retraits routes
    Route::prefix('retraits')->group(function () {
        Route::get('/', [RetraitController::class, 'index']);
        Route::post('/', [RetraitController::class, 'store']);
        Route::get('/{id}', [RetraitController::class, 'show']);
    });
    
    // Commandes routes
    Route::prefix('commandes')->group(function () {
        Route::get('/', [CommandeController::class, 'index']);
        Route::post('/', [CommandeController::class, 'store']);
        Route::get('/{id}', [CommandeController::class, 'show']);
        Route::put('/{id}/status', [CommandeController::class, 'updateStatus']);
    });
    
    // Messages routes
    Route::prefix('messages')->group(function () {
        Route::get('/conversations', [MessageController::class, 'conversations']);
        Route::get('/{userId}', [MessageController::class, 'messages']);
        Route::post('/', [MessageController::class, 'store']);
        Route::put('/{userId}/read', [MessageController::class, 'markAsRead']);
    });
    
    // Transactions/Paiements routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('/{id}', [TransactionController::class, 'show']);
        Route::post('/initialize', [TransactionController::class, 'initialize']);
        Route::get('/{id}/verify', [TransactionController::class, 'verify']);
        Route::get('/{id}/receipt', [TransactionController::class, 'downloadReceipt']);
    });
    
    // Payment callbacks (public for webhooks)
    Route::prefix('payment')->group(function () {
        Route::post('/callback/{provider}', [TransactionController::class, 'callback']);
    });
    
    // Avis routes
    Route::prefix('avis')->group(function () {
        Route::get('/', [AvisController::class, 'index']);
        Route::get('/prestataire/{prestataireId}', [AvisController::class, 'index']);
        Route::post('/', [AvisController::class, 'store']);
        Route::get('/{id}', [AvisController::class, 'show']);
    });
    
    // Notifications routes
    Route::prefix('notifications')->group(function () {
        Route::post('/register-token', [NotificationController::class, 'registerToken']);
        Route::post('/unregister-token', [NotificationController::class, 'unregisterToken']);
        Route::get('/preferences', [NotificationController::class, 'getPreferences']);
        Route::put('/preferences', [NotificationController::class, 'updatePreferences']);
    });
    
    // Position/GPS routes
    Route::prefix('positions')->group(function () {
        Route::post('/update', [PositionController::class, 'updatePosition']);
        Route::get('/commande/{commandeId}', [PositionController::class, 'getPosition']);
    });
    
    // Client favorites routes
    Route::prefix('client')->group(function () {
        // Favorite addresses
        Route::get('/favorite-addresses', [ClientController::class, 'getFavoriteAddresses']);
        Route::post('/favorite-addresses', [ClientController::class, 'addFavoriteAddress']);
        Route::put('/favorite-addresses/{addressId}', [ClientController::class, 'updateFavoriteAddress']);
        Route::delete('/favorite-addresses/{addressId}', [ClientController::class, 'deleteFavoriteAddress']);
        
        // Favorite prestataires
        Route::get('/favorite-prestataires', [ClientController::class, 'getFavoritePrestataires']);
        Route::post('/favorite-prestataires/{prestataireId}', [ClientController::class, 'addFavoritePrestataire']);
        Route::delete('/favorite-prestataires/{prestataireId}', [ClientController::class, 'removeFavoritePrestataire']);
    });
    
    // Route/Directions routes
    Route::prefix('routes')->group(function () {
        Route::get('/directions', [RouteController::class, 'getRoute']);
    });
});

