<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard with system overview.
     */
    public function index(GeneralSettings $settings)
    {
        // User metrics
        $metrics = [
            'total_users' => User::count(),
            'admins' => User::role('admin')->count(),
            'regular_users' => User::role('user')->count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_week' => User::whereBetween('created_at', [now()->subWeek(), now()])->count(),
        ];

        // Role metrics
        $roleMetrics = Role::withCount('users')->get()->pluck('users_count', 'name');

        // Recent activity (last 10)
        $recentActivity = Activity::with('causer')
            ->latest()
            ->take(10)
            ->get();

        // Recent signups (last 10)
        $recentSignups = User::role('user')
            ->latest()
            ->take(10)
            ->get();

        // System info
        $systemInfo = [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
        ];

        return view('admin.dashboard', compact(
            'settings',
            'metrics',
            'roleMetrics',
            'recentActivity',
            'recentSignups',
            'systemInfo'
        ));
    }

    /**
     * Redirect to Telescope.
     */
    public function logs()
    {
        return redirect('/telescope');
    }
}
