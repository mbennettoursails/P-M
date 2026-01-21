<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Admin Navigation -->
        <nav class="bg-gray-800 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo & Brand -->
                    <div class="flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center">
                            <svg class="h-8 w-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <span class="ml-2 text-xl font-semibold">Admin Panel</span>
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden md:flex items-center space-x-1">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.users') }}" 
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.users*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            Users
                        </a>
                        <a href="{{ route('admin.settings.index') }}" 
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.settings*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            Settings
                        </a>
                        <a href="{{ route('admin.roles.index') }}" 
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.roles*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            Roles
                        </a>
                        <a href="{{ route('admin.audit-logs.index') }}" 
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.audit-logs*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            Audit Logs
                        </a>
                        <a href="{{ route('admin.system-health.index') }}" 
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.system-health*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            System
                        </a>
                        <a href="/telescope" target="_blank"
                           class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">
                            Telescope ↗
                        </a>
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('dashboard') }}" 
                           class="text-sm text-gray-300 hover:text-white">
                            ← Back to Site
                        </a>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    class="flex items-center text-sm font-medium text-gray-300 hover:text-white focus:outline-none">
                                <span>{{ Auth::user()->name }}</span>
                                <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                <a href="{{ route('profile.edit') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" 
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div class="md:hidden" x-data="{ mobileOpen: false }">
                <button @click="mobileOpen = !mobileOpen" class="w-full px-4 py-2 text-left text-gray-300 border-t border-gray-700">
                    <span x-show="!mobileOpen">☰ Menu</span>
                    <span x-show="mobileOpen">✕ Close</span>
                </button>
                <div x-show="mobileOpen" class="px-2 pb-3 space-y-1 border-t border-gray-700">
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-gray-700">Dashboard</a>
                    <a href="{{ route('admin.users') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-gray-700">Users</a>
                    <a href="{{ route('admin.settings.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-gray-700">Settings</a>
                    <a href="{{ route('admin.roles.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-gray-700">Roles</a>
                    <a href="{{ route('admin.audit-logs.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-gray-700">Audit Logs</a>
                    <a href="{{ route('admin.system-health.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-gray-700">System</a>
                </div>
            </div>
        </nav>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-gray-500">
                    {{ config('app.name', 'Laravel') }} Admin Panel &copy; {{ date('Y') }}
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
