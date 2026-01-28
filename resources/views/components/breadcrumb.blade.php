@props(['items' => []])

@if(count($items) > 0)
<nav aria-label="Breadcrumb" class="flex">
    <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
        {{-- Home Link --}}
        <li>
            <a href="{{ route('dashboard') }}" wire:navigate class="hover:text-green-600 dark:hover:text-green-400 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="sr-only">{{ __('ホーム') }}</span>
            </a>
        </li>
        
        @foreach($items as $item)
            <li class="flex items-center">
                {{-- Separator --}}
                <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                
                @if(isset($item['url']) && !$loop->last)
                    <a 
                        href="{{ $item['url'] }}" 
                        wire:navigate
                        class="hover:text-green-600 dark:hover:text-green-400 transition"
                    >
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="text-gray-700 dark:text-gray-300 font-medium" aria-current="page">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif