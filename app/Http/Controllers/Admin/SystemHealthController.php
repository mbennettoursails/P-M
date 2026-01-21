<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SystemHealthController extends Controller
{
    /**
     * Display the system health dashboard.
     */
    public function index()
    {
        $health = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];

        $phpInfo = [
            'version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'loaded_extensions' => get_loaded_extensions(),
        ];

        $laravelInfo = [
            'version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'url' => config('app.url'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
        ];

        $diskUsage = $this->getDiskUsage();
        $queueStats = $this->getQueueStats();

        return view('admin.system-health.index', compact(
            'health',
            'phpInfo',
            'laravelInfo',
            'diskUsage',
            'queueStats'
        ));
    }

    /**
     * Check database connection.
     */
    protected function checkDatabase(): array
    {
        try {
            $startTime = microtime(true);
            DB::connection()->getPdo();
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'status' => 'healthy',
                'message' => 'Database connection successful',
                'response_time' => $responseTime . 'ms',
                'driver' => config('database.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Database connection failed: ' . $e->getMessage(),
                'response_time' => null,
                'driver' => config('database.default'),
            ];
        }
    }

    /**
     * Check cache connection.
     */
    protected function checkCache(): array
    {
        try {
            $startTime = microtime(true);
            $key = 'health_check_' . time();
            Cache::put($key, 'test', 10);
            $value = Cache::get($key);
            Cache::forget($key);
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            if ($value === 'test') {
                return [
                    'status' => 'healthy',
                    'message' => 'Cache is working',
                    'response_time' => $responseTime . 'ms',
                    'driver' => config('cache.default'),
                ];
            }

            return [
                'status' => 'unhealthy',
                'message' => 'Cache read/write failed',
                'response_time' => $responseTime . 'ms',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Cache error: ' . $e->getMessage(),
                'response_time' => null,
                'driver' => config('cache.default'),
            ];
        }
    }

    /**
     * Check storage.
     */
    protected function checkStorage(): array
    {
        try {
            $startTime = microtime(true);
            $testFile = 'health_check_' . time() . '.txt';
            Storage::put($testFile, 'test');
            $content = Storage::get($testFile);
            Storage::delete($testFile);
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            if ($content === 'test') {
                return [
                    'status' => 'healthy',
                    'message' => 'Storage is working',
                    'response_time' => $responseTime . 'ms',
                    'driver' => config('filesystems.default'),
                ];
            }

            return [
                'status' => 'unhealthy',
                'message' => 'Storage read/write failed',
                'response_time' => $responseTime . 'ms',
                'driver' => config('filesystems.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Storage error: ' . $e->getMessage(),
                'response_time' => null,
                'driver' => config('filesystems.default'),
            ];
        }
    }

    /**
     * Check queue connection.
     */
    protected function checkQueue(): array
    {
        $driver = config('queue.default');

        try {
            if ($driver === 'sync') {
                return [
                    'status' => 'healthy',
                    'message' => 'Using synchronous queue (no background processing)',
                    'driver' => $driver,
                ];
            }

            if ($driver === 'database') {
                $pending = DB::table('jobs')->count();
                $failed = DB::table('failed_jobs')->count();

                return [
                    'status' => 'healthy',
                    'message' => "Queue operational. Pending: {$pending}, Failed: {$failed}",
                    'driver' => $driver,
                    'pending_jobs' => $pending,
                    'failed_jobs' => $failed,
                ];
            }

            return [
                'status' => 'healthy',
                'message' => 'Queue configured',
                'driver' => $driver,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Queue error: ' . $e->getMessage(),
                'driver' => $driver,
            ];
        }
    }

    /**
     * Get disk usage information.
     */
    protected function getDiskUsage(): array
    {
        $path = storage_path();
        $total = disk_total_space($path);
        $free = disk_free_space($path);
        $used = $total - $free;

        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'percentage' => round(($used / $total) * 100, 1),
        ];
    }

    /**
     * Get queue statistics.
     */
    protected function getQueueStats(): array
    {
        if (config('queue.default') !== 'database') {
            return [];
        }

        try {
            return [
                'pending' => DB::table('jobs')->count(),
                'failed' => DB::table('failed_jobs')->count(),
                'recent_failed' => DB::table('failed_jobs')
                    ->latest('failed_at')
                    ->take(5)
                    ->get(['id', 'queue', 'failed_at']),
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Format bytes to human readable format.
     */
    protected function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Clear application cache.
     */
    public function clearCache()
    {
        Cache::flush();

        activity()
            ->causedBy(auth()->user())
            ->log('Application cache cleared');

        return back()->with('success', 'Application cache cleared successfully.');
    }

    /**
     * Clear config cache.
     */
    public function clearConfig()
    {
        Artisan::call('config:clear');

        activity()
            ->causedBy(auth()->user())
            ->log('Configuration cache cleared');

        return back()->with('success', 'Configuration cache cleared successfully.');
    }

    /**
     * Clear view cache.
     */
    public function clearViews()
    {
        Artisan::call('view:clear');

        activity()
            ->causedBy(auth()->user())
            ->log('View cache cleared');

        return back()->with('success', 'View cache cleared successfully.');
    }

    /**
     * Clear route cache.
     */
    public function clearRoutes()
    {
        Artisan::call('route:clear');

        activity()
            ->causedBy(auth()->user())
            ->log('Route cache cleared');

        return back()->with('success', 'Route cache cleared successfully.');
    }
}
