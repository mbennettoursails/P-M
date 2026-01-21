<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SystemHealthController;
use App\Http\Controllers\DecisionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Decisions\ProposalList;
use App\Livewire\Decisions\ProposalCreate;
use App\Livewire\Decisions\ProposalShow;
use App\Livewire\Decisions\ProposalEdit;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| User Dashboard Routes
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Decisions (Decider) Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Livewire Pages
    Route::prefix('decisions')->name('decisions.')->group(function () {
        Route::get('/', ProposalList::class)->name('index');
        Route::get('/create', ProposalCreate::class)->name('create');
        Route::get('/{proposal:uuid}', ProposalShow::class)->name('show');
        Route::get('/{proposal:uuid}/edit', ProposalEdit::class)->name('edit');
    });
    
    // API-style routes for AJAX actions
    Route::prefix('api/decisions')->name('api.decisions.')->group(function () {
        Route::post('/{proposal:uuid}/vote', [DecisionController::class, 'vote'])->name('vote');
        Route::post('/{proposal:uuid}/advance-stage', [DecisionController::class, 'advanceStage'])->name('advance-stage');
        Route::post('/{proposal:uuid}/close', [DecisionController::class, 'close'])->name('close');
        Route::post('/{proposal:uuid}/withdraw', [DecisionController::class, 'withdraw'])->name('withdraw');
        Route::post('/{proposal:uuid}/comments', [DecisionController::class, 'addComment'])->name('comments.store');
        Route::post('/{proposal:uuid}/documents', [DecisionController::class, 'uploadDocument'])->name('documents.store');
        Route::delete('/documents/{document}', [DecisionController::class, 'deleteDocument'])->name('documents.destroy');
        Route::post('/{proposal:uuid}/invite', [DecisionController::class, 'invite'])->name('invite');
        Route::post('/{proposal:uuid}/remind', [DecisionController::class, 'sendReminders'])->name('remind');
    });
    
});

/*
|--------------------------------------------------------------------------
| Notification Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification:uuid}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
});

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| Protected by auth and admin middleware
| All routes prefixed with /admin and named with admin.*
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::get('/users', [AdminUsersController::class, 'index'])->name('users');
    Route::get('/users/{user}', [AdminUsersController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/toggle-admin', [AdminUsersController::class, 'toggleAdmin'])->name('users.toggle-admin');
    
    // Settings Management
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    
    // Roles & Permissions Management
    Route::get('/roles', [RolesController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RolesController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RolesController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}/edit', [RolesController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RolesController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RolesController::class, 'destroy'])->name('roles.destroy');
    
    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{activity}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    Route::post('/audit-logs/clear', [AuditLogController::class, 'clear'])->name('audit-logs.clear');
    
    // System Health
    Route::get('/system-health', [SystemHealthController::class, 'index'])->name('system-health.index');
    Route::post('/system-health/clear-cache', [SystemHealthController::class, 'clearCache'])->name('system-health.clear-cache');
    Route::post('/system-health/clear-config', [SystemHealthController::class, 'clearConfig'])->name('system-health.clear-config');
    Route::post('/system-health/clear-views', [SystemHealthController::class, 'clearViews'])->name('system-health.clear-views');
    Route::post('/system-health/clear-routes', [SystemHealthController::class, 'clearRoutes'])->name('system-health.clear-routes');
    
    // Telescope (redirect)
    Route::get('/logs', [AdminDashboardController::class, 'logs'])->name('logs');
});

require __DIR__.'/auth.php';