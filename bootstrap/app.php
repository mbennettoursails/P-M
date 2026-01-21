<?php

use App\Http\Middleware\CheckRegistration;
use App\Http\Middleware\IsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // CRITICAL: Trust proxies for HTTPS detection behind load balancer
        $middleware->trustProxies(at: '*');
        
        // Register middleware aliases
        $middleware->alias([
            'admin' => IsAdmin::class,
            'registration' => CheckRegistration::class,
            
            // Spatie Permission middleware (auto-registered but explicit for clarity)
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        // Prune old activity log entries (keep 90 days)
        $schedule->command('activitylog:clean --days=90')
                 ->daily()
                 ->at('03:00');
    })
    ->create();
