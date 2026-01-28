{{-- Toast Notification Component --}}
@props(['type' => 'success', 'message'])

@php
$colors = [
    'success' => [
        'bg' => 'bg-green-50 dark:bg-green-900/20',
        'border' => 'border-green-400 dark:border-green-600',
        'text' => 'text-green-800 dark:text-green-200',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />',
        'iconBg' => 'bg-green-400 dark:bg-green-600'
    ],
    'error' => [
        'bg' => 'bg-red-50 dark:bg-red-900/20',
        'border' => 'border-red-400 dark:border-red-600',
        'text' => 'text-red-800 dark:text-red-200',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />',
        'iconBg' => 'bg-red-400 dark:bg-red-600'
    ],
    'warning' => [
        'bg' => 'bg-yellow-50 dark:bg-yellow-900/20',
        'border' => 'border-yellow-400 dark:border-yellow-600',
        'text' => 'text-yellow-800 dark:text-yellow-200',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
        'iconBg' => 'bg-yellow-400 dark:bg-yellow-600'
    ],
    'info' => [
        'bg' => 'bg-blue-50 dark:bg-blue-900/20',
        'border' => 'border-blue-400 dark:border-blue-600',
        'text' => 'text-blue-800 dark:text-blue-200',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'iconBg' => 'bg-blue-400 dark:bg-blue-600'
    ]
];

$style = $colors[$type] ?? $colors['info'];
@endphp

<div x-data="{ show: true, progress: 100 }"
     x-show="show"
     x-init="
        setTimeout(() => {
            let interval = setInterval(() => {
                progress -= 2;
                if (progress <= 0) {
                    clearInterval(interval);
                    show = false;
                }
            }, 100);
        }, 100);
     "
     x-transition:enter="transform ease-out duration-300 transition"
     x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
     x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 {{ $style['bg'] }} border {{ $style['border'] }}"
     role="alert">
    <div class="p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <div class="h-8 w-8 rounded-full {{ $style['iconBg'] }} flex items-center justify-center">
                    <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        {!! $style['icon'] !!}
                    </svg>
                </div>
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
                <p class="text-sm font-medium {{ $style['text'] }}">
                    {{ $message }}
                </p>
            </div>
            <div class="ml-4 flex flex-shrink-0">
                <button @click="show = false"
                        class="inline-flex rounded-md {{ $style['text'] }} hover:opacity-75 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span class="sr-only">Close</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <!-- Progress bar -->
    <div class="h-1 bg-gray-200 dark:bg-gray-700">
        <div class="{{ $style['iconBg'] }} h-full transition-all duration-100 ease-linear"
             :style="`width: ${progress}%`"></div>
    </div>
</div>