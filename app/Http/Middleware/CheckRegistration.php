<?php

namespace App\Http\Middleware;

use App\Settings\GeneralSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRegistration
{
    /**
     * Handle an incoming request.
     *
     * Checks if user registration is enabled in settings.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settings = app(GeneralSettings::class);

        if (!$settings->registration_enabled) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'User registration is currently disabled.',
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', 'User registration is currently disabled. Please contact an administrator.');
        }

        return $next($request);
    }
}
