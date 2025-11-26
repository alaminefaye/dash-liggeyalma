<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\PrestataireController;
use App\Http\Controllers\Admin\CategorieServiceController;
use App\Http\Controllers\Admin\SousCategorieServiceController;
use App\Http\Controllers\Admin\CommandeController;
use App\Http\Controllers\Admin\AvisController;
use App\Http\Controllers\Admin\LitigeController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\SoldeNegatifController;
use App\Http\Controllers\Admin\AntiFraudeController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\RetraitController;
use App\Http\Controllers\Admin\ContournementController;
use App\Http\Controllers\Admin\ParametreController;
use App\Http\Controllers\Admin\RapportController;

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes (User Dashboard)
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard Admin
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Clients
    Route::resource('clients', ClientController::class);
    Route::get('/clients/export', [ClientController::class, 'export'])->name('clients.export');
    Route::post('/clients/{client}/suspend', [ClientController::class, 'suspend'])->name('clients.suspend');
    Route::post('/clients/{client}/activate', [ClientController::class, 'activate'])->name('clients.activate');
    
    // Prestataires
    Route::get('/prestataires/pending', [PrestataireController::class, 'pending'])->name('prestataires.pending');
    Route::get('/prestataires/export', [PrestataireController::class, 'export'])->name('prestataires.export');
    Route::resource('prestataires', PrestataireController::class);
    Route::post('/prestataires/{prestataire}/validate', [PrestataireController::class, 'validate'])->name('prestataires.validate');
    Route::post('/prestataires/{prestataire}/reject', [PrestataireController::class, 'reject'])->name('prestataires.reject');
    Route::post('/prestataires/{prestataire}/suspend', [PrestataireController::class, 'suspend'])->name('prestataires.suspend');
    Route::post('/prestataires/{prestataire}/activate', [PrestataireController::class, 'activate'])->name('prestataires.activate');
    Route::post('/prestataires/{prestataire}/block', [PrestataireController::class, 'block'])->name('prestataires.block');
    Route::post('/prestataires/{prestataire}/unblock', [PrestataireController::class, 'unblock'])->name('prestataires.unblock');
    
    // Catégories de Services
    Route::resource('categories', CategorieServiceController::class);
    
    // Sous-Catégories de Services
    Route::resource('sous-categories', SousCategorieServiceController::class);
    
    // Commandes
    Route::resource('commandes', CommandeController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::get('/commandes/export', [CommandeController::class, 'export'])->name('commandes.export');
    
    // Avis
    Route::resource('avis', AvisController::class)->only(['index', 'show', 'destroy']);
    Route::post('/avis/{avi}/approve', [AvisController::class, 'approve'])->name('avis.approve');
    Route::post('/avis/{avi}/hide', [AvisController::class, 'hide'])->name('avis.hide');
    
    // Litiges
    Route::resource('litiges', LitigeController::class)->only(['index', 'show']);
    Route::post('/litiges/{litige}/process', [LitigeController::class, 'process'])->name('litiges.process');
    Route::post('/litiges/{litige}/resolve', [LitigeController::class, 'resolve'])->name('litiges.resolve');
    Route::post('/litiges/{litige}/close', [LitigeController::class, 'close'])->name('litiges.close');
    
    // Commissions
    Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index');
    Route::post('/commissions', [CommissionController::class, 'update'])->name('commissions.update');
    Route::get('/commissions/statistics', [CommissionController::class, 'statistics'])->name('commissions.statistics');
    Route::get('/commissions/reports', [CommissionController::class, 'reports'])->name('commissions.reports');
    
    // Soldes Négatifs
    Route::get('/soldes-negatifs', [SoldeNegatifController::class, 'index'])->name('soldes-negatifs.index');
    Route::get('/soldes-negatifs/{prestataire}', [SoldeNegatifController::class, 'show'])->name('soldes-negatifs.show');
    Route::post('/soldes-negatifs/{prestataire}/force-payment', [SoldeNegatifController::class, 'forcePayment'])->name('soldes-negatifs.force-payment');
    Route::post('/soldes-negatifs/{prestataire}/block', [SoldeNegatifController::class, 'block'])->name('soldes-negatifs.block');
    Route::post('/soldes-negatifs/{prestataire}/send-reminder', [SoldeNegatifController::class, 'sendReminder'])->name('soldes-negatifs.send-reminder');
    
    // Anti-Fraude Statistiques
    Route::get('/anti-fraude/statistiques', [AntiFraudeController::class, 'statistics'])->name('anti-fraude.statistiques');
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/{id}/handle', [NotificationController::class, 'handle'])->name('notifications.handle');
    Route::post('/commandes/{commande}/update-status', [CommandeController::class, 'updateStatus'])->name('commandes.update-status');
    Route::post('/commandes/{commande}/cancel', [CommandeController::class, 'cancel'])->name('commandes.cancel');
    Route::get('/commandes/{commande}/invoice', [CommandeController::class, 'generateInvoice'])->name('commandes.invoice');
    
    // Transactions
    Route::resource('transactions', TransactionController::class)->only(['index', 'show']);
    Route::get('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
    Route::post('/transactions/{transaction}/validate', [TransactionController::class, 'validate'])->name('transactions.validate');
    Route::post('/transactions/{transaction}/reject', [TransactionController::class, 'reject'])->name('transactions.reject');
    Route::post('/transactions/{transaction}/refund', [TransactionController::class, 'refund'])->name('transactions.refund');
    
    // Retraits
    Route::resource('retraits', RetraitController::class)->only(['index', 'show']);
    Route::post('/retraits/{retrait}/validate', [RetraitController::class, 'validate'])->name('retraits.validate');
    Route::post('/retraits/{retrait}/reject', [RetraitController::class, 'reject'])->name('retraits.reject');
    
    // Contournements (Anti-fraude)
    Route::resource('contournements', ContournementController::class)->only(['index', 'show']);
    Route::post('/contournements/{contournement}/validate', [ContournementController::class, 'validate'])->name('contournements.validate');
    Route::post('/contournements/{contournement}/reject', [ContournementController::class, 'reject'])->name('contournements.reject');
    Route::post('/contournements/{contournement}/warn', [ContournementController::class, 'warn'])->name('contournements.warn');
    Route::post('/contournements/{contournement}/block', [ContournementController::class, 'block'])->name('contournements.block');
    
    // Paramètres
    Route::get('/parametres', [ParametreController::class, 'index'])->name('parametres.index');
    Route::post('/parametres', [ParametreController::class, 'update'])->name('parametres.update');
    Route::post('/parametres/initialize', [ParametreController::class, 'initialize'])->name('parametres.initialize');
    Route::get('/parametres/anti-contournement', [ParametreController::class, 'antiContournement'])->name('parametres.anti-contournement');
    Route::post('/parametres/anti-contournement', [ParametreController::class, 'updateAntiContournement'])->name('parametres.anti-contournement.update');
    
    // Rapports
    Route::get('/rapports', [RapportController::class, 'index'])->name('rapports.index');
    Route::get('/rapports/financier', [RapportController::class, 'financier'])->name('rapports.financier');
    Route::get('/rapports/utilisateurs', [RapportController::class, 'utilisateurs'])->name('rapports.utilisateurs');
    Route::get('/rapports/commandes', [RapportController::class, 'commandes'])->name('rapports.commandes');
    Route::get('/rapports/services', [RapportController::class, 'services'])->name('rapports.services');
    Route::get('/rapports/export', [RapportController::class, 'export'])->name('rapports.export');
});
