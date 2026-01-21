@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Audit Logs</h1>
                    <p class="mt-1 text-sm text-gray-600">Track all system activity and changes</p>
                </div>
                <form method="POST" 
                      action="{{ route('admin.audit-logs.clear') }}" 
                      onsubmit="return confirm('Clear old audit logs?')"
                      class="flex items-center space-x-2">
                    @csrf
                    <input type="number" 
                           name="older_than_days" 
                           value="90" 
                           min="1" 
                           max="365"
                           class="w-20 rounded-md border-gray-300 shadow-sm text-sm">
                    <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">
                        Clear Older Than Days
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <form method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search description..."
                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                </div>
                <div class="w-36">
                    <select name="event" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">All Events</option>
                        @foreach($events as $event)
                            <option value="{{ $event }}" {{ request('event') === $event ? 'selected' : '' }}>
                                {{ ucfirst($event) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-36">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                           placeholder="From">
                </div>
                <div class="w-36">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                           class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                           placeholder="To">
                </div>
                <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">
                    Filter
                </button>
                <a href="{{ route('admin.audit-logs.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">
                    Clear
                </a>
            </form>
        </div>

        <!-- Activity List -->
        <div class="bg-white rounded-lg shadow">
            @forelse($activities as $activity)
                <div class="px-6 py-4 border-b border-gray-200 hover:bg-gray-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">{{ $activity->description }}</p>
                            <div class="mt-1 flex items-center space-x-3 text-xs text-gray-500">
                                <span>{{ $activity->causer?->name ?? 'System' }}</span>
                                @if($activity->subject_type)
                                    <span>•</span>
                                    <span>{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</span>
                                @endif
                                @if($activity->event)
                                    <span>•</span>
                                    <span class="px-1.5 py-0.5 bg-gray-100 rounded">{{ $activity->event }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500">{{ $activity->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-400">{{ $activity->created_at->format('g:i A') }}</p>
                        </div>
                    </div>
                    @if($activity->properties && $activity->properties->count() > 0)
                        <details class="mt-2">
                            <summary class="text-xs text-blue-600 cursor-pointer hover:text-blue-800">View details</summary>
                            <pre class="mt-2 bg-gray-100 text-gray-700 p-3 rounded text-xs overflow-x-auto">{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
                        </details>
                    @endif
                </div>
            @empty
                <div class="px-6 py-12 text-center text-gray-500">
                    <p>No activity logs found</p>
                </div>
            @endforelse

            @if($activities->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $activities->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
