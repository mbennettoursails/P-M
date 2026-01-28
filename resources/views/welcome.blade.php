<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- PWA Head --}}
        @PwaHead

        <title>{{ config('app.name', '北東京CO-OP Hub') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-japanese bg-gradient-to-br from-primary-50 via-white to-primary-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 min-h-screen">
        {{-- Navigation --}}
        <nav class="fixed top-0 left-0 right-0 z-50 safe-top">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    {{-- Logo --}}
                    <div class="flex items-center">
                        <a href="/" class="flex items-center space-x-2">
                            <img 
                                src="{{ asset('images/icons/android/android-launchericon-48-48.png') }}" 
                                alt="{{ config('app.name') }}"
                                class="h-10 w-10 rounded-xl shadow-sm"
                                onerror="this.style.display='none'"
                            >
                            <span class="text-xl font-bold text-gray-900 dark:text-white hidden sm:inline">
                                {{ config('app.name', '北東京CO-OP Hub') }}
                            </span>
                        </a>
                    </div>

                    {{-- Auth Links --}}
                    @if (Route::has('login'))
                        <div class="flex items-center space-x-2 sm:space-x-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" 
                                   class="btn btn-primary text-sm">
                                    {{ __('ダッシュボード') }}
                                </a>
                            @else
                                <a href="{{ route('login') }}" 
                                   class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 px-3 py-2 touch-target flex items-center">
                                    {{ __('ログイン') }}
                                </a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" 
                                       class="btn btn-primary text-sm">
                                        {{ __('新規登録') }}
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </nav>

        {{-- Hero Section --}}
        <main class="pt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row items-center justify-center min-h-[calc(100vh-4rem)] py-12 lg:py-0 gap-8 lg:gap-16">
                    
                    {{-- Text Content --}}
                    <div class="flex-1 text-center lg:text-left max-w-xl">
                        {{-- Badge --}}
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300 mb-6">
                            <span class="w-2 h-2 bg-primary-500 rounded-full mr-2 animate-pulse"></span>
                            {{ __('コミュニティプラットフォーム') }}
                        </div>

                        {{-- Heading --}}
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white leading-tight mb-4">
                            {{ __('北東京') }}<br class="hidden sm:inline">
                            <span class="text-primary-600 dark:text-primary-400">CO-OP Hub</span>
                        </h1>

                        {{-- Description --}}
                        <p class="text-base sm:text-lg text-gray-600 dark:text-gray-300 mb-8 leading-relaxed">
                            {{ __('北東京生活クラブ生活協同組合連合会のメンバー向けコミュニティプラットフォームです。ニュース、イベント、相互扶助、意思決定をサポートします。') }}
                        </p>

                        {{-- CTA Buttons --}}
                        <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="btn btn-primary">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                    </svg>
                                    {{ __('ダッシュボードへ') }}
                                </a>
                            @else
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn btn-primary">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                        </svg>
                                        {{ __('メンバー登録') }}
                                    </a>
                                @endif
                                <a href="{{ route('login') }}" class="btn btn-outline">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                    </svg>
                                    {{ __('ログイン') }}
                                </a>
                            @endauth
                        </div>

                        {{-- Features List --}}
                        <div class="mt-10 grid grid-cols-2 gap-4 text-left">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-primary-100 dark:bg-primary-900/50 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('ニュース') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('最新情報をチェック') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-primary-100 dark:bg-primary-900/50 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('イベント') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('活動に参加') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-primary-100 dark:bg-primary-900/50 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('コミュニティ') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('相互扶助') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-primary-100 dark:bg-primary-900/50 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('意思決定') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('提案と投票') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Visual/Image --}}
                    <div class="flex-1 max-w-md lg:max-w-lg">
                        <div class="relative">
                            {{-- Decorative background --}}
                            <div class="absolute inset-0 bg-gradient-to-r from-primary-400 to-primary-600 rounded-3xl transform rotate-3 opacity-20"></div>
                            <div class="absolute inset-0 bg-gradient-to-l from-primary-300 to-primary-500 rounded-3xl transform -rotate-3 opacity-10"></div>
                            
                            {{-- Main card --}}
                            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 sm:p-8">
                                {{-- App icon --}}
                                <div class="flex justify-center mb-6">
                                    <img 
                                        src="{{ asset('images/icons/android/android-launchericon-192-192.png') }}" 
                                        alt="{{ config('app.name') }}"
                                        class="w-24 h-24 sm:w-32 sm:h-32 rounded-3xl shadow-lg"
                                        onerror="this.parentElement.innerHTML='<div class=\'w-24 h-24 sm:w-32 sm:h-32 bg-primary-600 rounded-3xl flex items-center justify-center\'><span class=\'text-4xl sm:text-5xl text-white font-bold\'>C</span></div>'"
                                    >
                                </div>

                                {{-- Install prompt --}}
                                <div class="text-center">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                        {{ __('アプリをインストール') }}
                                    </h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                        {{ __('ホーム画面に追加して、いつでもすぐにアクセスできます。') }}
                                    </p>
                                    
                                    {{-- PWA Install Button --}}
                                    <div class="flex justify-center">
                                        @PwaInstallButton
                                    </div>
                                </div>

                                {{-- Decorative dots --}}
                                <div class="flex justify-center mt-6 space-x-2">
                                    <div class="w-2 h-2 bg-primary-500 rounded-full"></div>
                                    <div class="w-2 h-2 bg-primary-300 rounded-full"></div>
                                    <div class="w-2 h-2 bg-primary-200 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        {{-- Footer --}}
        <footer class="safe-bottom py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                    <p>&copy; {{ date('Y') }} {{ __('北東京生活クラブ生活協同組合') }}</p>
                    <p class="mt-1 text-xs">{{ __('プライバシーを第一に考えたプラットフォーム') }}</p>
                </div>
            </div>
        </footer>

        {{-- Service Worker Registration --}}
        @RegisterServiceWorkerScript
    </body>
</html>