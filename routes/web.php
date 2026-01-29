<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SystemHealthController;
use App\Http\Controllers\KnowledgeAttachmentController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Decisions\ProposalCreate;
use App\Livewire\Decisions\ProposalEdit;
use App\Livewire\Decisions\ProposalList;
use App\Livewire\Decisions\ProposalShow;
use App\Livewire\Events\EventForm;
use App\Livewire\Events\EventShow;
use App\Livewire\Events\EventsIndex;
use App\Livewire\Knowledge\KnowledgeCreate;
use App\Livewire\Knowledge\KnowledgeEdit;
use App\Livewire\Knowledge\KnowledgeIndex;
use App\Livewire\Knowledge\KnowledgeShow;
use App\Livewire\News\NewsCreate;
use App\Livewire\News\NewsEdit;
use App\Livewire\News\NewsList;
use App\Livewire\News\NewsShow;
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
| Authenticated & Verified User Routes
|--------------------------------------------------------------------------
| All member-facing features require authentication and email verification
*/
Route::middleware(['auth', 'verified'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | News Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('news')->name('news.')->group(function () {
        Route::get('/', NewsList::class)->name('index');
        Route::get('/create', NewsCreate::class)->name('create');
        Route::get('/{news:uuid}', NewsShow::class)->name('show');
        Route::get('/{news:uuid}/edit', NewsEdit::class)->name('edit');
    });

    /*
    |--------------------------------------------------------------------------
    | Events Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/', EventsIndex::class)->name('index');
        Route::get('/create', EventForm::class)
            ->middleware('can:create,App\Models\Event')
            ->name('create');
        Route::get('/{event:uuid}', EventShow::class)->name('show');
        Route::get('/{event:uuid}/edit', EventForm::class)
            ->middleware('can:update,event')
            ->name('edit');
    });

    /*
    |--------------------------------------------------------------------------
    | Knowledge Base Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('knowledge')->name('knowledge.')->group(function () {
        Route::get('/', KnowledgeIndex::class)->name('index');
        Route::get('/create', KnowledgeCreate::class)->name('create');
        Route::get('/{article:uuid}', KnowledgeShow::class)->name('show');
        Route::get('/{article:uuid}/edit', KnowledgeEdit::class)->name('edit');
        Route::get('/attachment/{attachment:uuid}/download', [KnowledgeAttachmentController::class, 'download'])
            ->name('attachment.download');
    });

    /*
    |--------------------------------------------------------------------------
    | Decisions (Proposals) Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('decisions')->name('decisions.')->group(function () {
        Route::get('/', ProposalList::class)->name('index');
        Route::get('/create', ProposalCreate::class)->name('create');
        Route::get('/{proposal:uuid}', ProposalShow::class)->name('show');
        Route::get('/{proposal:uuid}/edit', ProposalEdit::class)->name('edit');
    });

    /*
    |--------------------------------------------------------------------------
    | Profile Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
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
