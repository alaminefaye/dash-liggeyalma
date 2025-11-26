<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategorieController;
use App\Http\Controllers\Api\PrestataireController;
use App\Http\Controllers\Api\CommandeController;
use App\Http\Controllers\Api\MessageController;

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
});

