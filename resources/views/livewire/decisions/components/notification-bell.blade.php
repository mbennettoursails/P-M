<div x-data="{ open: @entangle('isOpen') }" class="relative">
    {{-- Bell Button --}}
    <button @click="open = !open"
            class="relative p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
        <x-heroicon-o-bell class="w-6 h-6" />
        
        {{-- Unread Badge --}}
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold text-white bg-red-500 rounded-full">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-lg border border-gray-200 z-50">
        
        {{-- Header --}}
        <div class="flex items-center justify-between p-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">{{ __('decisions.notifications.title') }}</h3>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead"
                        class="text-xs text-coop-600 hover:text-coop-700">
                    {{ __('decisions.notifications.mark_all_read') }}
                </button>
            @endif
        </div>

        {{-- Notifications List --}}
        <div class="max-h-96 overflow-y-auto">
            @forelse($notifications as $notification)
                <div wire:click="goToNotification('{{ $notification->uuid }}')"
                     class="flex items-start p-4 hover:bg-gray-50 cursor-pointer transition-colors border-b border-gray-50 last:border-0
                            {{ $notification->is_unread ? 'bg-coop-50/50' : '' }}">
                    {{-- Icon --}}
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-{{ $notification->icon_color }}-100 flex items-center justify-center">
                        <x-dynamic-component :component="'heroicon-o-' . $notification->icon" 
                                             class="w-5 h-5 text-{{ $notification->icon_color }}-600" />
                    </div>

                    {{-- Content --}}
                    <div class="ml-3 flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 {{ $notification->is_unread ? '' : 'font-normal' }}">
                            {{ $notification->localized_title }}
                        </p>
                        <p class="text-sm text-gray-500 mt-1 line-clamp-2">
                            {{ $notification->localized_message }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $notification->time_ago }}
                        </p>
                    </div>

                    {{-- Unread Indicator --}}
                    @if($notification->is_unread)
                        <div class="flex-shrink-0 ml-2">
                            <span class="w-2 h-2 bg-coop-500 rounded-full block"></span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <x-heroicon-o-bell-slash class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                    <p>{{ __('decisions.notifications.no_notifications') }}</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        @if($notifications->isNotEmpty())
            <div class="p-3 border-t border-gray-100">
                <a href="{{ route('notifications.index') }}"
                   class="block text-center text-sm text-coop-600 hover:text-coop-700">
                    {{ __('decisions.notifications.view_all') }}
                </a>
            </div>
        @endif
    </div>
</div>
