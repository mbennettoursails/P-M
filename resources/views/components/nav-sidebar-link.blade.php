@props([
    'active' => false,
    'href' => '#',
    'badge' => null,
    'badgeColor' => 'red',
    'collapsed' => false,
])

@php
    $baseClasses = 'flex items-center px-3 py-3 rounded-lg transition-all duration-200 group min-h-[48px]';
    
    $activeClasses = $active 
        ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 font-medium' 
        : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white';
    
    $badgeColorClasses = match($badgeColor) {
        'green' => 'bg-green-500 text-white',
        'blue' => 'bg-blue-500 text-white',
        'purple' => 'bg-purple-500 text-white',
        'yellow' => 'bg-yellow-500 text-white',
        'orange' => 'bg-orange-500 text-white',
        default => 'bg-red-500 text-white',
    };
@endphp

<a 
    href="{{ $href }}" 
    wire:navigate
    {{ $attributes->merge(['class' => $baseClasses . ' ' . $activeClasses]) }}
    x-data
>
    {{-- Icon Slot --}}
    <span class="flex-shrink-0 {{ $active ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300' }}">
        {{ $icon ?? '' }}
    </span>
    
    {{-- Label --}}
    <span 
        x-show="!$el.closest('[x-data]')?.collapsed && !$el.closest('aside')?.classList.contains('w-20')"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-x-2"
        x-transition:enter-end="opacity-100 translate-x-0"
        class="ml-3 whitespace-nowrap"
    >
        {{ $slot }}
    </span>
    
    {{-- Badge --}}
    @if($badge && $badge > 0)
        <span 
            x-show="!$el.closest('[x-data]')?.collapsed && !$el.closest('aside')?.classList.contains('w-20')"
            x-transition
            class="ml-auto px-2 py-0.5 text-xs font-bold rounded-full {{ $badgeColorClasses }}"
        >
            {{ $badge > 99 ? '99+' : $badge }}
        </span>
        
        {{-- Collapsed badge (dot indicator) --}}
        <span 
            x-show="$el.closest('[x-data]')?.collapsed || $el.closest('aside')?.classList.contains('w-20')"
            class="absolute top-1 right-1 w-2 h-2 rounded-full {{ $badgeColorClasses }}"
        ></span>
    @endif
</a>