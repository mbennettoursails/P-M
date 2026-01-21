@extends('layouts.app')

@section('title', __('decisions.notifications.title'))

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ __('decisions.notifications.title') }}</h1>
            
            @if($notifications->where('read_at', null)->count() > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm text-coop-600 hover:text-coop-700">
                        {{ __('decisions.notifications.mark_all_read') }}
                    </button>
                </form>
            @endif
        </div>

        {{-- Notifications List --}}
        @if($notifications->isEmpty())
            <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                <x-heroicon-o-bell-slash class="w-16 h-16 mx-auto text-gray-300 mb-4" />
                <p class="text-gray-500">{{ __('decisions.notifications.no_notifications') }}</p>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm divide-y divide-gray-100">
                @foreach($notifications as $notification)
                    <a href="{{ $notification->action_url ?? '#' }}"
                       onclick="event.preventDefault(); markAndNavigate('{{ $notification->uuid }}', '{{ $notification->action_url }}')"
                       class="flex items-start p-4 hover:bg-gray-50 transition-colors {{ $notification->is_unread ? 'bg-coop-50/50' : '' }}">
                        {{-- Icon --}}
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-{{ $notification->icon_color }}-100 flex items-center justify-center">
                            <x-dynamic-component :component="'heroicon-o-' . $notification->icon" 
                                                 class="w-6 h-6 text-{{ $notification->icon_color }}-600" />
                        </div>

                        {{-- Content --}}
                        <div class="ml-4 flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <p class="text-sm font-medium text-gray-900 {{ $notification->is_unread ? '' : 'font-normal' }}">
                                    {{ $notification->localized_title }}
                                </p>
                                @if($notification->is_unread)
                                    <span class="ml-2 w-2 h-2 bg-coop-500 rounded-full flex-shrink-0"></span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $notification->localized_message }}
                            </p>
                            <p class="text-xs text-gray-400 mt-2">
                                {{ $notification->created_at->format('Y/m/d H:i') }}
                                <span class="mx-1">•</span>
                                {{ $notification->time_ago }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function markAndNavigate(uuid, url) {
    fetch(`/notifications/${uuid}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    }).finally(() => {
        if (url) {
            window.location.href = url;
        }
    });
}
</script>
@endsection
