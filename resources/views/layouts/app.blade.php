<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        {{-- ============================================= --}}
        {{-- PWA Head - Includes manifest, theme-color,   --}}
        {{-- apple-touch-icon, and other PWA meta tags    --}}
        {{-- ============================================= --}}
        @PwaHead

        <title>{{ config('app.name', '北東京CO-OP Hub') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        {{-- Japanese Font Support --}}
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Custom Styles for Safe Area (iOS notch support) --}}
        <style>
            .safe-area-top {
                padding-top: env(safe-area-inset-top);
            }
            .safe-area-bottom {
                padding-bottom: env(safe-area-inset-bottom);
            }
            .safe-area-left {
                padding-left: env(safe-area-inset-left);
            }
            .safe-area-right {
                padding-right: env(safe-area-inset-right);
            }
            
            /* Ensure minimum touch target size for accessibility */
            .touch-target {
                min-height: 48px;
                min-width: 48px;
            }
            
            /* Japanese text optimization */
            .text-japanese {
                font-family: 'Noto Sans JP', 'Figtree', sans-serif;
                word-break: keep-all;
                overflow-wrap: break-word;
            }
            
            /* Sidebar content offset helper */
            .sidebar-offset {
                transition: margin-left 0.3s ease-in-out;
            }
            
            /* PWA Install Button positioning (when shown) */
            .pwa-install-container {
                position: fixed;
                bottom: 100px;
                right: 16px;
                z-index: 40;
            }
            
            @media (min-width: 1024px) {
                .pwa-install-container {
                    bottom: 24px;
                }
            }
        </style>

        @livewireStyles
    </head>
    <body class="font-sans antialiased text-japanese">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            {{-- Navigation Component --}}
            @auth
                <livewire:layout.navigation />
            @endauth

            {{-- Main Content Wrapper --}}
            {{-- Desktop: offset for sidebar, Mobile: offset for header and bottom nav --}}
            <div 
                id="main-content"
                class="sidebar-offset lg:ml-64 transition-all duration-300 pt-16 lg:pt-0 pb-20 lg:pb-0"
            >
                <!-- Page Heading with Breadcrumbs -->
                @isset($header)
                    <header class="bg-white dark:bg-gray-800 shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{-- Breadcrumbs (if provided) --}}
                            @isset($breadcrumbs)
                                <div class="mb-4">
                                    <x-breadcrumb :items="$breadcrumbs" />
                                </div>
                            @endisset
                            
                            {{-- Page Title --}}
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main>
                    {{-- Toast Notifications Container (Fixed Position) --}}
                    <div aria-live="assertive" class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 z-50">
                        <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
                            {{-- Flash Messages - Using inline toast styles if component doesn't exist --}}
                            @if (session('success'))
                                <div class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5">
                                    <div class="p-4">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('成功') }}</p>
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ session('success') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5">
                                    <div class="p-4">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                                </svg>
                                            </div>
                                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('エラー') }}</p>
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ session('error') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (session('warning'))
                                <div class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5">
                                    <div class="p-4">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                                </svg>
                                            </div>
                                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('警告') }}</p>
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ session('warning') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (session('info'))
                                <div class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5">
                                    <div class="p-4">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                                </svg>
                                            </div>
                                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('お知らせ') }}</p>
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ session('info') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5">
                                    <div class="p-4">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                                </svg>
                                            </div>
                                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('入力エラー') }}</p>
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('入力内容にエラーがあります。') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Main Content Slot --}}
                    {{ $slot }}
                </main>

                {{-- Footer (Desktop Only - Hidden on Mobile due to bottom nav) --}}
                <footer class="hidden lg:block bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-12">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-600 dark:text-gray-400">
                            <div class="mb-4 md:mb-0">
                                <p>&copy; {{ date('Y') }} {{ __('北東京生活クラブ生活協同組合') }}.</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ __('プライバシーを第一に考えたプラットフォーム') }}</p>
                            </div>
                            <div class="flex space-x-6">
                                {{-- Only show links that exist --}}
                                @if(Route::has('privacy'))
                                    <a href="{{ route('privacy') }}" class="hover:text-green-600 dark:hover:text-green-400 transition">{{ __('プライバシーポリシー') }}</a>
                                @endif
                                @if(Route::has('terms'))
                                    <a href="{{ route('terms') }}" class="hover:text-green-600 dark:hover:text-green-400 transition">{{ __('利用規約') }}</a>
                                @endif
                                @if(Route::has('help'))
                                    <a href="{{ route('help') }}" class="hover:text-green-600 dark:hover:text-green-400 transition">{{ __('ヘルプ') }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
            
            {{-- ============================================= --}}
            {{-- PWA Install Button (Optional - shows when    --}}
            {{-- install prompt is available)                 --}}
            {{-- Remove this if you don't want the button     --}}
            {{-- ============================================= --}}
            <div class="pwa-install-container">
                @PwaInstallButton
            </div>
        </div>

        @livewireScripts
        
        {{-- ============================================= --}}
        {{-- PWA Service Worker Registration              --}}
        {{-- This MUST be after @livewireScripts          --}}
        {{-- ============================================= --}}
        @RegisterServiceWorkerScript
        
        {{-- Sidebar Collapse Handler --}}
        <script>
            // Listen for sidebar collapse changes and adjust main content
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('sidebar-collapsed', (collapsed) => {
                    const mainContent = document.getElementById('main-content');
                    if (mainContent) {
                        if (collapsed) {
                            mainContent.classList.remove('lg:ml-64');
                            mainContent.classList.add('lg:ml-20');
                        } else {
                            mainContent.classList.remove('lg:ml-20');
                            mainContent.classList.add('lg:ml-64');
                        }
                    }
                });
            });
            
            // Auto-dismiss toasts after 5 seconds
            document.addEventListener('DOMContentLoaded', () => {
                const toasts = document.querySelectorAll('[aria-live="assertive"] > div > div');
                toasts.forEach(toast => {
                    setTimeout(() => {
                        toast.style.opacity = '0';
                        toast.style.transform = 'translateX(100%)';
                        toast.style.transition = 'all 0.3s ease-out';
                        setTimeout(() => toast.remove(), 300);
                    }, 5000);
                });
            });
        </script>
        
        @stack('scripts')
    </body>
</html>