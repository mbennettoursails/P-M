@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                    <p class="mt-1 text-sm text-gray-600">Welcome back, {{ Auth::user()->name }}</p>
                </div>
                <div class="text-sm text-gray-500">
                    {{ now()->format('l, F j, Y') }}
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($metrics['total_users']) }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Admins -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Admins</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($metrics['admins']) }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Regular Users -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Regular Users</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($metrics['regular_users']) }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- New Today -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">New Today</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($metrics['new_users_today']) }}</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- New This Week -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">New This Week</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($metrics['new_users_week']) }}</p>
                    </div>
                    <div class="p-3 bg-indigo-100 rounded-full">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Activity -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
                    <a href="{{ route('admin.audit-logs.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                        View All →
                    </a>
                </div>
                
                @if($recentActivity->isEmpty())
                    <div class="px-6 py-12 text-center text-gray-500">
                        <p>No recent activity</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-200">
                        @foreach($recentActivity as $activity)
                            <div class="px-6 py-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-900">{{ $activity->description }}</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            by {{ $activity->causer?->name ?? 'System' }}
                                        </p>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Quick Actions & System Info -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
                    </div>
                    <div class="p-4 space-y-2">
                        <a href="{{ route('admin.users') }}" 
                           class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Manage Users</span>
                        </a>
                        <a href="{{ route('admin.settings.index') }}" 
                           class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <svg class="w-5 h-5 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">App Settings</span>
                        </a>
                        <a href="{{ route('admin.roles.index') }}" 
                           class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <svg class="w-5 h-5 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Manage Roles</span>
                        </a>
                        <a href="{{ route('admin.system-health.index') }}" 
                           class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">System Health</span>
                        </a>
                    </div>
                </div>

                <!-- System Info -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">System Info</h2>
                    </div>
                    <div class="p-6">
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Laravel</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $systemInfo['laravel_version'] }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">PHP</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $systemInfo['php_version'] }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Environment</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $systemInfo['environment'] }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Debug</dt>
                                <dd class="text-sm font-medium {{ $systemInfo['debug_mode'] ? 'text-yellow-600' : 'text-green-600' }}">
                                    {{ $systemInfo['debug_mode'] ? 'Enabled' : 'Disabled' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Signups -->
        <div class="mt-8 bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">Recent Signups</h2>
                <a href="{{ route('admin.users') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    View All Users →
                </a>
            </div>
            
            @if($recentSignups->isEmpty())
                <div class="px-6 py-12 text-center text-gray-500">
                    <p>No users have signed up yet</p>
                </div>
            @else
                <div class="divide-y divide-gray-200">
                    @foreach($recentSignups as $user)
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-blue-600 font-medium text-sm">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
                                <a href="{{ route('admin.users.show', $user) }}" class="text-xs text-blue-600 hover:text-blue-800">
                                    View Details
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- App Settings Summary -->
        <div class="mt-8 bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">Current Settings</h2>
                <a href="{{ route('admin.settings.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    Edit Settings →
                </a>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">App Name</p>
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $settings->app_name }}</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Timezone</p>
                        <p class="text-sm font-medium text-gray-900">{{ $settings->timezone }}</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Maintenance</p>
                        <p class="text-sm font-medium {{ $settings->maintenance_mode ? 'text-red-600' : 'text-green-600' }}">
                            {{ $settings->maintenance_mode ? 'ON' : 'OFF' }}
                        </p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Registration</p>
                        <p class="text-sm font-medium {{ $settings->registration_enabled ? 'text-green-600' : 'text-red-600' }}">
                            {{ $settings->registration_enabled ? 'Open' : 'Closed' }}
                        </p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Default Role</p>
                        <p class="text-sm font-medium text-gray-900 capitalize">{{ $settings->default_user_role }}</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Logo</p>
                        <p class="text-sm font-medium {{ $settings->app_logo ? 'text-green-600' : 'text-gray-400' }}">
                            {{ $settings->app_logo ? 'Set' : 'Not Set' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
