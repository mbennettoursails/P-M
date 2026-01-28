<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Livewire\Knowledge\KnowledgeIndex;
use App\Livewire\Knowledge\KnowledgeShow;
use App\Livewire\Knowledge\KnowledgeCreate;
use App\Livewire\Knowledge\KnowledgeEdit;
use App\Http\Controllers\KnowledgeAttachmentController;
use App\Livewire\News\NewsList;
use App\Livewire\News\NewsShow;
use App\Livewire\News\NewsCreate;
use App\Livewire\News\NewsEdit;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SystemHealthController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Decisions\ProposalList;
use App\Livewire\Decisions\ProposalShow;
use App\Livewire\Decisions\ProposalCreate;
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
| Livewire components handle all CRUD operations
| No traditional controllers needed - Livewire components ARE the controllers
*/
Route::middleware(['auth', 'verified'])->prefix('decisions')->name('decisions.')->group(function () {
    
    // List all proposals
    Route::get('/', ProposalList::class)->name('index');
    
    // Create new proposal
    Route::get('/create', ProposalCreate::class)->name('create');
    
    // View single proposal (uses UUID for cleaner URLs)
    Route::get('/{proposal:uuid}', ProposalShow::class)->name('show');
    
    // Edit proposal
    Route::get('/{proposal:uuid}/edit', ProposalEdit::class)->name('edit');
});

// News Routes (public viewing, authenticated for full access)
Route::middleware(['auth', 'verified'])->prefix('news')->name('news.')->group(function () {
    // List all news
    Route::get('/', NewsList::class)->name('index');
    
    // Create new article (requires create permission)
    Route::get('/create', NewsCreate::class)->name('create');
    
    // View single article (uses UUID for cleaner URLs)
    Route::get('/{news:uuid}', NewsShow::class)->name('show');
    
    // Edit article (requires update permission)
    Route::get('/{news:uuid}/edit', NewsEdit::class)->name('edit');
});

// Knowledge Base Routes
Route::middleware(['auth', 'verified'])->prefix('knowledge')->name('knowledge.')->group(function () {
    // Browse/Search
    Route::get('/', KnowledgeIndex::class)->name('index');
    
    // Create (requires permission)
    Route::get('/create', KnowledgeCreate::class)->name('create');
    
    // View article
    Route::get('/{article:uuid}', KnowledgeShow::class)->name('show');
    
    // Edit (requires permission)
    Route::get('/{article:uuid}/edit', KnowledgeEdit::class)->name('edit');
    
    // Download attachment
    Route::get('/attachment/{attachment:uuid}/download', [KnowledgeAttachmentController::class, 'download'])
        ->name('attachment.download');
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
