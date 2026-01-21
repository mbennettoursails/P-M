@extends('layouts.admin')

@section('title', 'System Health')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <h1 class="text-3xl font-bold text-gray-900">System Health</h1>
            <p class="mt-1 text-sm text-gray-600">Monitor system status and manage caches</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Health Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @foreach($health as $service => $status)
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-medium text-gray-600 capitalize">{{ $service }}</h3>
                        @if($status['status'] === 'healthy')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                ✓ Healthy
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                ✗ Unhealthy
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500">{{ $status['message'] }}</p>
                    @if(isset($status['response_time']))
                        <p class="text-xs text-gray-400 mt-1">Response: {{ $status['response_time'] }}</p>
                    @endif
                    @if(isset($status['driver']))
                        <p class="text-xs text-gray-400 mt-1">Driver: {{ $status['driver'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Laravel Info -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Laravel Information</h2>
                </div>
                <div class="p-6">
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Version</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $laravelInfo['version'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Environment</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $laravelInfo['environment'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Debug Mode</dt>
                            <dd class="text-sm font-medium {{ $laravelInfo['debug_mode'] ? 'text-yellow-600' : 'text-green-600' }}">
                                {{ $laravelInfo['debug_mode'] ? 'Enabled' : 'Disabled' }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">URL</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $laravelInfo['url'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Timezone</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $laravelInfo['timezone'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Locale</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $laravelInfo['locale'] }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- PHP Info -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">PHP Information</h2>
                </div>
                <div class="p-6">
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Version</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $phpInfo['version'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Memory Limit</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $phpInfo['memory_limit'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Max Execution Time</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $phpInfo['max_execution_time'] }}s</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Upload Max Filesize</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $phpInfo['upload_max_filesize'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Post Max Size</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $phpInfo['post_max_size'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Loaded Extensions</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ count($phpInfo['loaded_extensions']) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Disk Usage -->
        <div class="mt-8 bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Disk Usage</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600">Storage Usage</span>
                    <span class="text-sm font-medium text-gray-900">{{ $diskUsage['used'] }} / {{ $diskUsage['total'] }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div class="h-4 rounded-full {{ $diskUsage['percentage'] > 90 ? 'bg-red-600' : ($diskUsage['percentage'] > 70 ? 'bg-yellow-500' : 'bg-green-600') }}" 
                         style="width: {{ $diskUsage['percentage'] }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">{{ $diskUsage['free'] }} free ({{ 100 - $diskUsage['percentage'] }}%)</p>
            </div>
        </div>

        <!-- Cache Management -->
        <div class="mt-8 bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Cache Management</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <form method="POST" action="{{ route('admin.system-health.clear-cache') }}">
                        @csrf
                        <button type="submit" 
                                class="w-full px-4 py-3 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition text-sm font-medium"
                                onclick="return confirm('Clear application cache?')">
                            Clear App Cache
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.system-health.clear-config') }}">
                        @csrf
                        <button type="submit" 
                                class="w-full px-4 py-3 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition text-sm font-medium"
                                onclick="return confirm('Clear config cache?')">
                            Clear Config Cache
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.system-health.clear-views') }}">
                        @csrf
                        <button type="submit" 
                                class="w-full px-4 py-3 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition text-sm font-medium"
                                onclick="return confirm('Clear view cache?')">
                            Clear View Cache
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.system-health.clear-routes') }}">
                        @csrf
                        <button type="submit" 
                                class="w-full px-4 py-3 bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100 transition text-sm font-medium"
                                onclick="return confirm('Clear route cache?')">
                            Clear Route Cache
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
