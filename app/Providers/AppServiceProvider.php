<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use App\Models\Event;
use App\Policies\EventPolicy;
use App\Models\News;
use App\Policies\NewsPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
      
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS URLs in production (CRITICAL for DigitalOcean App Platform)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            $this->ensureStorageLink();
        }

        // Register admin gate for easy authorization checks
        Gate::define('access-admin', function ($user) {
            return $user->role === 'admin';
        });
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(News::class, NewsPolicy::class);


        // Note: viewTelescope gate is defined in TelescopeServiceProvider
        // Note: viewHorizon gate is defined in HorizonServiceProvider (if installed)

        
    }

    /**
     * Ensure storage symlink exists and uses relative path.
     * This fixes issues with absolute paths created during build phase.
     */
    protected function ensureStorageLink(): void
    {
        $target = '../storage/app/public'; // Relative path
        $link = public_path('storage');

        // Remove existing symlink if it's broken or uses absolute path
        if (is_link($link)) {
            $currentTarget = readlink($link);
            
            // If it's an absolute path or the link is broken, recreate it
            if (str_starts_with($currentTarget, '/') || !file_exists($link)) {
                @unlink($link);
            } else {
                // Symlink is good (relative and working), skip
                return;
            }
        }

        // Create relative symlink if it doesn't exist
        if (!file_exists($link)) {
            @symlink($target, $link);
        }
    }
}