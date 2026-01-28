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
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        {{-- Japanese Font Support --}}
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        {{-- Custom Styles --}}
        <style>
            /* Japanese text optimization */
            .text-japanese {
                font-family: 'Noto Sans JP', 'Figtree', sans-serif;
                word-break: keep-all;
                overflow-wrap: break-word;
            }
            
            /* Safe area for iOS */
            .safe-area-bottom {
                padding-bottom: env(safe-area-inset-bottom);
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased text-japanese">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900 safe-area-bottom">
            {{-- Logo/Brand --}}
            <div>
                <a href="/" class="flex flex-col items-center">
                    {{-- App Icon for PWA feel --}}
                    <img 
                        src="{{ asset('images/icons/icon-192x192.png') }}" 
                        alt="{{ config('app.name') }}"
                        class="w-20 h-20 rounded-2xl shadow-lg mb-2"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                    >
                    {{-- Fallback to application logo component --}}
                    <div style="display: none;">
                        <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                    </div>
                    {{-- App Name --}}
                    <span class="text-xl font-bold text-green-600 dark:text-green-400 mt-2">
                        {{ config('app.name', '北東京CO-OP Hub') }}
                    </span>
                </a>
            </div>

            {{-- Main Card --}}
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
            
            {{-- Footer Links --}}
            <div class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
                <p>&copy; {{ date('Y') }} {{ __('北東京生活クラブ生活協同組合') }}</p>
                <div class="mt-2 space-x-4">
                    @if(Route::has('privacy'))
                        <a href="{{ route('privacy') }}" class="hover:text-green-600 dark:hover:text-green-400 transition">
                            {{ __('プライバシーポリシー') }}
                        </a>
                    @endif
                    @if(Route::has('help'))
                        <a href="{{ route('help') }}" class="hover:text-green-600 dark:hover:text-green-400 transition">
                            {{ __('ヘルプ') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- ============================================= --}}
        {{-- PWA Service Worker Registration              --}}
        {{-- Important: Include on guest pages too so     --}}
        {{-- the PWA can be installed from login screen   --}}
        {{-- ============================================= --}}
        @RegisterServiceWorkerScript
    </body>
</html>