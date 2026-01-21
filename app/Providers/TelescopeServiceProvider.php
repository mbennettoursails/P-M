<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');
        $isDebugMode = config('telescope.debug_mode', false); // New: Debug mode toggle

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal, $isDebugMode) {
            // LOCAL: Log everything
            if ($isLocal) {
                return true;
            }

            // PRODUCTION DEBUG MODE: Log everything temporarily for troubleshooting
            if ($isDebugMode) {
                return true;
            }

            // PRODUCTION NORMAL MODE: Log only important events
            return $entry->isReportableException() ||
                   $entry->isFailedRequest() ||
                   $entry->isFailedJob() ||
                   $entry->isScheduledTask() ||
                   $entry->hasMonitoredTag();
        });

        // Tag specific service calls for easy filtering
        Telescope::tag(function (IncomingEntry $entry) {
            // Tag Claude API calls
            if ($entry->type === 'request' && str_contains($entry->content['uri'] ?? '', 'anthropic.com')) {
                return ['claude-api', 'ai-service'];
            }

            // Tag Gemini API calls
            if ($entry->type === 'request' && str_contains($entry->content['uri'] ?? '', 'generativelanguage.googleapis.com')) {
                return ['gemini-api', 'ai-service', 'image-generation'];
            }

            // Tag file storage operations
            if ($entry->type === 'event' && str_contains($entry->content['name'] ?? '', 'Storage')) {
                return ['storage', 'file-operations'];
            }

            return [];
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token', 'password', 'password_confirmation']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
            'authorization',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            return in_array($user->email, [
                'mjbennett14@gmail.com',
            ]);
        });
    }
}