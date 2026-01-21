<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomSecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * Adds security headers to all responses to protect against common vulnerabilities.
     * Uses different CSP policies for development vs production environments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        
        // Enable XSS protection in older browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // Referrer Policy - don't send full URL to external sites
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        
        // Content Security Policy (CSP) - Different for dev vs production
        if (app()->environment('local', 'development')) {
            // DEVELOPMENT: Permissive CSP for Vite dev server
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:* http://127.0.0.1:* http://[::1]:* https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
                "style-src 'self' 'unsafe-inline' http://localhost:* http://127.0.0.1:* http://[::1]:* https://cdn.jsdelivr.net https://fonts.googleapis.com",
                "img-src 'self' data: https: http: blob:",
                "font-src 'self' data: http://localhost:* http://127.0.0.1:* http://[::1]:* https://fonts.gstatic.com https://cdn.jsdelivr.net",
                "connect-src 'self' ws://localhost:* ws://127.0.0.1:* ws://[::1]:* http://localhost:* http://127.0.0.1:* http://[::1]:* https://api.anthropic.com https://generativelanguage.googleapis.com",
                "frame-src 'self'",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'"
            ]);
        } else {
            // PRODUCTION: Strict CSP for security
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
                "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
                "img-src 'self' data: https: blob:",
                "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net",
                "connect-src 'self' https://api.anthropic.com https://generativelanguage.googleapis.com",
                "frame-src 'self'",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'"
            ]);
        }
        
        $response->headers->set('Content-Security-Policy', $csp);
        
        // HSTS - Force HTTPS for 1 year (ONLY in production)
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
        
        // Remove server identification headers
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
        
        // Permissions Policy - restrict browser features
        $permissionsPolicy = implode(', ', [
            'accelerometer=()',
            'camera=()',
            'geolocation=()',
            'gyroscope=()',
            'magnetometer=()',
            'microphone=()',
            'payment=()',
            'usb=()'
        ]);
        
        $response->headers->set('Permissions-Policy', $permissionsPolicy);
        
        return $response;
    }
}