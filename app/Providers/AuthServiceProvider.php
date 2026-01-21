<?php

namespace App\Providers;

use App\Models\Hunt;
use App\Policies\HuntPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application
     */
    protected $policies = [
        Hunt::class => HuntPolicy::class,
    ];

    /**
     * Register any authentication / authorization services
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Note: viewTelescope gate is defined in TelescopeServiceProvider
        // This prevents gate definition conflicts
    }
}