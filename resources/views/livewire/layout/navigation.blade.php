<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

new class extends Component
{
    public bool $sidebarCollapsed = false;
    public bool $moreMenuOpen = false;
    
    // Badge counts (can be updated via Livewire)
    public int $newsCount = 0;
    public int $communityCount = 0;
    public int $proposalCount = 0;
    public int $pendingContentCount = 0;
    
    public function mount(): void
    {
        $user = auth()->user();
        
        if ($user) {
            // Load badge counts based on role
            $this->loadBadgeCounts($user);
        }
        
        // Load sidebar state from session
        $this->sidebarCollapsed = session('sidebar_collapsed', false);
    }
    
    protected function loadBadgeCounts($user): void
    {
        // News count - unread news for this user
        // TODO: Uncomment when News model exists
        // $this->newsCount = \App\Models\News::unreadFor($user)->count();
        $this->newsCount = 0; // Will be implemented later
        
        // Community requests count
        // TODO: Uncomment when MutualAidRequest model exists
        // $this->communityCount = \App\Models\MutualAidRequest::active()->count();
        $this->communityCount = 0; // Will be implemented later
        
        // Proposal counts for Reijikai/Admin - using current_stage column
        if ($user->hasAnyRole(['reijikai', 'admin'])) {
            try {
                // Count proposals that are in the voting stage
                $this->proposalCount = \App\Models\Proposal::where('current_stage', 'voting')
                    ->where(function($q) {
                        $q->whereNull('voting_deadline')
                          ->orWhere('voting_deadline', '>', now());
                    })
                    ->count();
            } catch (\Exception $e) {
                // If query fails (table doesn't exist, column missing, etc.), default to 0
                Log::warning('Navigation: Failed to load proposal count', ['error' => $e->getMessage()]);
                $this->proposalCount = 0;
            }
        }
        
        // Pending content for Shokuin
        if ($user->hasAnyRole(['shokuin', 'admin'])) {
            // TODO: Uncomment when Content model exists
            // $this->pendingContentCount = \App\Models\Content::pending()->count();
            $this->pendingContentCount = 0; // Will be implemented later
        }
    }
    
    public function toggleSidebar(): void
    {
        $this->sidebarCollapsed = !$this->sidebarCollapsed;
        session(['sidebar_collapsed' => $this->sidebarCollapsed]);
        
        // Dispatch event for layout to listen to
        $this->dispatch('sidebar-collapsed', $this->sidebarCollapsed);
    }
    
    public function toggleMoreMenu(): void
    {
        $this->moreMenuOpen = !$this->moreMenuOpen;
    }
    
    public function closeMoreMenu(): void
    {
        $this->moreMenuOpen = false;
    }
    
    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->redirect(route('login'), navigate: true);
    }
}; ?>

{{-- ============================================
     DESKTOP SIDEBAR NAVIGATION
     ============================================ --}}
<div>
    {{-- Desktop Sidebar --}}
    <aside 
        x-data="{ collapsed: @entangle('sidebarCollapsed') }"
        :class="collapsed ? 'w-20' : 'w-64'"
        class="fixed left-0 top-0 h-screen bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 
               transition-all duration-300 ease-in-out z-40
               hidden lg:flex lg:flex-col"
>

        {{-- Logo & Brand --}}
        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center space-x-3 overflow-hidden">
                {{-- COOP Logo --}}
                <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div x-show="!collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex flex-col leading-tight">
                    <span class="text-lg font-bold text-gray-800 dark:text-white whitespace-nowrap">{{ __('北東京CO-OP') }}</span>
                    <span class="text-xs text-green-600 dark:text-green-400 font-medium">Hub</span>
                </div>
            </a>
            
            {{-- Toggle Button --}}
            <button 
                wire:click="toggleSidebar"
                class=" rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                :class="collapsed ? 'rotate-180' : ''"
                title="{{ __('サイドバーを切り替え') }}"
            >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
            </button>
        </div>
        
        {{-- User Info --}}
        <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-3">
                {{-- Avatar --}}
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-green-700 dark:text-green-300 font-bold text-lg">
                        {{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </span>
                </div>
                <div x-show="!collapsed" x-transition class="overflow-hidden">
                    <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">{{ auth()->user()->name ?? 'Guest' }}</p>
                    {{-- Role Badge --}}
                    @php
                        $user = auth()->user();
                        $roleBadge = match(true) {
                            $user?->hasRole('admin') => ['text' => __('管理者'), 'class' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'],
                            $user?->hasRole('reijikai') => ['text' => __('委員会'), 'class' => 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300'],
                            $user?->hasRole('shokuin') => ['text' => __('職員'), 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'],
                            default => ['text' => __('ボランティア'), 'class' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'],
                        };
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $roleBadge['class'] }}">
                        {{ $roleBadge['text'] }}
                    </span>
                </div>
            </div>
        </div>
        
        {{-- Navigation Menu --}}
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            {{-- Dashboard --}}
            @php $isActive = request()->routeIs('dashboard'); @endphp
            <a 
                href="{{ route('dashboard') }}" 
                wire:navigate
                class="flex items-center px-3 py-3 rounded-lg transition-all duration-200 group min-h-[48px] relative
                       {{ $isActive ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 font-medium' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}"
            >
                <svg class="w-6 h-6 flex-shrink-0 {{ $isActive ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span x-show="!collapsed" x-transition class="ml-3 whitespace-nowrap">{{ __('ホーム') }}</span>
            </a>
            
            {{-- News (Coming Soon) --}}
            @php $isActive = request()->routeIs('news.*'); @endphp
            <a 
                href="{{ Route::has('news.index') ? route('news.index') : '#' }}" 
                @if(!Route::has('news.index')) onclick="event.preventDefault(); alert('{{ __('この機能は近日公開予定です') }}')" @else wire:navigate @endif
                class="flex items-center px-3 py-3 rounded-lg transition-all duration-200 group min-h-[48px] relative
                       {{ $isActive ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 font-medium' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}
                       {{ !Route::has('news.index') ? 'opacity-60' : '' }}"
            >
                <svg class="w-6 h-6 flex-shrink-0 {{ $isActive ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                </svg>
                <span x-show="!collapsed" x-transition class="ml-3 whitespace-nowrap">{{ __('ニュース') }}</span>
                @if($newsCount > 0 && Route::has('news.index'))
                    <span x-show="!collapsed" x-transition class="ml-auto px-2 py-0.5 text-xs font-bold rounded-full bg-red-500 text-white">
                        {{ $newsCount > 99 ? '99+' : $newsCount }}
                    </span>
                    <span x-show="collapsed" class="absolute top-1 right-1 w-2 h-2 rounded-full bg-red-500"></span>
                @endif
                @if(!Route::has('news.index'))
                    <span x-show="!collapsed" x-transition class="ml-auto text-xs text-gray-400">{{ __('準備中') }}</span>
                @endif
            </a>
            
            {{-- Community / Mutual Aid (Coming Soon) --}}
            @php $isActive = request()->routeIs('community.*'); @endphp
            <a 
                href="{{ Route::has('community.index') ? route('community.index') : '#' }}" 
                @if(!Route::has('community.index')) onclick="event.preventDefault(); alert('{{ __('この機能は近日公開予定です') }}')" @else wire:navigate @endif
                class="flex items-center px-3 py-3 rounded-lg transition-all duration-200 group min-h-[48px] relative
                       {{ $isActive ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 font-medium' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}
                       {{ !Route::has('community.index') ? 'opacity-60' : '' }}"
            >
                <svg class="w-6 h-6 flex-shrink-0 {{ $isActive ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span x-show="!collapsed" x-transition class="ml-3 whitespace-nowrap">{{ __('助け合い') }}</span>
                @if($communityCount > 0 && Route::has('community.index'))
                    <span x-show="!collapsed" x-transition class="ml-auto px-2 py-0.5 text-xs font-bold rounded-full bg-green-500 text-white">
                        {{ $communityCount > 99 ? '99+' : $communityCount }}
                    </span>
                    <span x-show="collapsed" class="absolute top-1 right-1 w-2 h-2 rounded-full bg-green-500"></span>
                @endif
                @if(!Route::has('community.index'))
                    <span x-show="!collapsed" x-transition class="ml-auto text-xs text-gray-400">{{ __('準備中') }}</span>
                @endif
            </a>
            
            {{-- Events (Coming Soon) --}}
            @php $isActive = request()->routeIs('events.*'); @endphp
            <a 
                href="{{ Route::has('events.index') ? route('events.index') : '#' }}" 
                @if(!Route::has('events.index')) onclick="event.preventDefault(); alert('{{ __('この機能は近日公開予定です') }}')" @else wire:navigate @endif
                class="flex items-center px-3 py-3 rounded-lg transition-all duration-200 group min-h-[48px] relative
                       {{ $isActive ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 font-medium' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}
                       {{ !Route::has('events.index') ? 'opacity-60' : '' }}"
            >
                <svg class="w-6 h-6 flex-shrink-0 {{ $isActive ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span x-show="!collapsed" x-transition class="ml-3 whitespace-nowrap">{{ __('イベント') }}</span>
                @if(!Route::has('events.index'))
                    <span x-show="!collapsed" x-transition class="ml-auto text-xs text-gray-400">{{ __('準備中') }}</span>
                @endif
            </a>
            
            {{-- Knowledge Base (Coming Soon) --}}
            @php $isActive = request()->routeIs('knowledge.*'); @endphp
            <a 
                href="{{ Route::has('knowledge.index') ? route('knowledge.index') : '#' }}" 
                @if(!Route::has('knowledge.index')) onclick="event.preventDefault(); alert('{{ __('この機能は近日公開予定です') }}')" @else wire:navigate @endif
                class="flex items-center px-3 py-3 rounded-lg transition-all duration-200 group min-h-[48px] relative
                       {{ $isActive ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 font-medium' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}
                       {{ !Route::has('knowledge.index') ? 'opacity-60' : '' }}"
            >
                <svg class="w-6 h-6 flex-shrink-0 {{ $isActive ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span x-show="!collapsed" x-transition class="ml-3 whitespace-nowrap">{{ __('知識倉庫') }}</span>
                @if(!Route::has('knowledge.index'))
                    <span x-show="!collapsed" x-transition class="ml-auto text-xs text-gray-400">{{ __('準備中') }}</span>
                @endif
            </a>
            
            {{-- Divider --}}
            <div class="my-4 border-t border-gray-200 dark:border-gray-700"></div>
            
            {{-- ROLE-SPECIFIC SECTIONS --}}
            
            {{-- Decisions - Reijikai & Admin Only --}}
            @if(auth()->user()?->hasAnyRole(['reijikai', 'admin']))
                @php $isActive = request()->routeIs('decisions.*'); @endphp
                <a 
                    href="{{ route('decisions.index') }}" 
                    wire:navigate
                    class="flex items-center px-3 py-3 rounded-lg transition-all duration-200 group min-h-[48px] relative
                           {{ $isActive ? 'bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 font-medium' : 'text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/30 hover:text-purple-800 dark:hover:text-purple-200' }}"
                >
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <span x-show="!collapsed" x-transition class="ml-3 whitespace-nowrap">{{ __('意思決定') }}</span>
                    @if($proposalCount > 0)
                        <span x-show="!collapsed" x-transition class="ml-auto px-2 py-0.5 text-xs font-bold rounded-full bg-purple-500 text-white">
                            {{ $proposalCount > 99 ? '99+' : $proposalCount }}
                        </span>
                        <span x-show="collapsed" class="absolute top-1 right-1 w-2 h-2 rounded-full bg-purple-500"></span>
                    @endif
                </a>
            @endif
            
            {{-- Content Management - Shokuin & Admin Only (Coming Soon) --}}
            @if(auth()->user()?->hasAnyRole(['shokuin', 'admin']))
                @php $isActive = request()->routeIs('content.*'); @endphp
                <a 
                    href="{{ Route::has('content.index') ? route('content.index') : '#' }}" 
                    @if(!Route::has('content.index')) onclick="event.preventDefault(); alert('{{ __('この機能は近日公開予定です') }}')" @else wire:navigate @endif
                    class="flex items-center px-3 py-3 rounded-lg transition-all duration-200 group min-h-[48px] relative
                           {{ $isActive ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 font-medium' : 'text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-800 dark:hover:text-blue-200' }}
                           {{ !Route::has('content.index') ? 'opacity-60' : '' }}"
                >
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <span x-show="!collapsed" x-transition class="ml-3 whitespace-nowrap">{{ __('コンテンツ管理') }}</span>
                    @if($pendingContentCount > 0 && Route::has('content.index'))
                        <span x-show="!collapsed" x-transition class="ml-auto px-2 py-0.5 text-xs font-bold rounded-full bg-blue-500 text-white">
                            {{ $pendingContentCount > 99 ? '99+' : $pendingContentCount }}
                        </span>
                        <span x-show="collapsed" class="absolute top-1 right-1 w-2 h-2 rounded-full bg-blue-500"></span>
                    @endif
                    @if(!Route::has('content.index'))
                        <span x-show="!collapsed" x-transition class="ml-auto text-xs text-gray-400">{{ __('準備中') }}</span>
                    @endif
                </a>
            @endif
            
            {{-- Admin Dashboard - Admin Only --}}
            @if(auth()->user()?->hasRole('admin'))
                <div class="my-4 border-t border-gray-200 dark:border-gray-700"></div>
                @php $isActive = request()->routeIs('admin.*'); @endphp
                <a 
                    href="{{ route('admin.dashboard') }}" 
                    wire:navigate
                    class="flex items-center px-3 py-3 rounded-lg transition-all duration-200 group min-h-[48px] relative
                           {{ $isActive ? 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 font-medium' : 'text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-800 dark:hover:text-red-200' }}"
                >
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <span x-show="!collapsed" x-transition class="ml-3 whitespace-nowrap font-semibold">{{ __('管理者') }}</span>
                </a>
            @endif
        </nav>
        
        {{-- Bottom Section - Profile & Logout --}}
        <div class="border-t border-gray-200 dark:border-gray-700 p-3 space-y-1">
            {{-- Profile --}}
            @php $isActive = request()->routeIs('profile.*'); @endphp
            <a 
                href="{{ route('profile.edit') }}" 
                wire:navigate
                class="flex items-center px-3 py-3 rounded-lg transition-all duration-200 group min-h-[48px]
                       {{ $isActive ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 font-medium' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}"
            >
                <svg class="w-6 h-6 flex-shrink-0 {{ $isActive ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span x-show="!collapsed" x-transition class="ml-3 whitespace-nowrap">{{ __('プロフィール') }}</span>
            </a>
            
            {{-- Logout --}}
            <button 
                wire:click="logout"
                class="w-full flex items-center px-3 py-3 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition group min-h-[48px]"
            >
                <svg class="w-6 h-6 flex-shrink-0 text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span x-show="!collapsed" x-transition class="ml-3">{{ __('ログアウト') }}</span>
            </button>
        </div>
    </aside>
    
    {{-- ============================================
         MOBILE HEADER
         ============================================ --}}
    <header class="lg:hidden fixed top-0 left-0 right-0 h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 z-40 flex items-center justify-between px-4 safe-area-top">
        {{-- Logo --}}
        <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <span class="text-lg font-bold text-gray-800 dark:text-white">{{ __('北東京CO-OP') }}</span>
        </a>
        
        {{-- Right Side: User Menu --}}
        <div class="flex items-center space-x-3">
            {{-- Role Badge --}}
            @php
                $user = auth()->user();
                $roleBadge = match(true) {
                    $user?->hasRole('admin') => ['text' => __('管理者'), 'class' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'],
                    $user?->hasRole('reijikai') => ['text' => __('委員会'), 'class' => 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300'],
                    $user?->hasRole('shokuin') => ['text' => __('職員'), 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'],
                    default => ['text' => __('ボランティア'), 'class' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'],
                };
            @endphp
            <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $roleBadge['class'] }}">
                {{ $roleBadge['text'] }}
            </span>
            
            {{-- User Avatar --}}
            <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center">
                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                    <span class="text-green-700 dark:text-green-300 font-bold text-sm">
                        {{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </span>
                </div>
            </a>
        </div>
    </header>
    
    {{-- ============================================
         MOBILE BOTTOM NAVIGATION
         ============================================ --}}
    <nav 
        x-data="{ moreOpen: @entangle('moreMenuOpen') }"
        class="lg:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-40"
    >
        <div class="flex items-center justify-around h-16 safe-area-bottom">
            {{-- Home --}}
            <a 
                href="{{ route('dashboard') }}" 
                wire:navigate
                class="flex flex-col items-center justify-center w-full h-full min-h-[48px] {{ request()->routeIs('dashboard') ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="text-xs mt-1">{{ __('ホーム') }}</span>
            </a>
            
            {{-- Decisions (Reijikai/Admin) or News (Others) --}}
            @if(auth()->user()?->hasAnyRole(['reijikai', 'admin']))
                <a 
                    href="{{ route('decisions.index') }}" 
                    wire:navigate
                    class="flex flex-col items-center justify-center w-full h-full min-h-[48px] relative {{ request()->routeIs('decisions.*') ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500 dark:text-gray-400' }}"
                >
                    <div class="relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        @if($proposalCount > 0)
                            <span class="absolute -top-1 -right-1 w-4 h-4 bg-purple-500 text-white text-xs rounded-full flex items-center justify-center">{{ $proposalCount }}</span>
                        @endif
                    </div>
                    <span class="text-xs mt-1">{{ __('意思決定') }}</span>
                </a>
            @else
                {{-- News for non-Reijikai users --}}
                <a 
                    href="{{ Route::has('news.index') ? route('news.index') : '#' }}" 
                    @if(!Route::has('news.index')) onclick="event.preventDefault(); alert('{{ __('この機能は近日公開予定です') }}')" @else wire:navigate @endif
                    class="flex flex-col items-center justify-center w-full h-full min-h-[48px] relative {{ request()->routeIs('news.*') ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }} {{ !Route::has('news.index') ? 'opacity-60' : '' }}"
                >
                    <div class="relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                        @if($newsCount > 0 && Route::has('news.index'))
                            <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">{{ $newsCount }}</span>
                        @endif
                    </div>
                    <span class="text-xs mt-1">{{ __('ニュース') }}</span>
                </a>
            @endif
            
            {{-- Community --}}
            <a 
                href="{{ Route::has('community.index') ? route('community.index') : '#' }}" 
                @if(!Route::has('community.index')) onclick="event.preventDefault(); alert('{{ __('この機能は近日公開予定です') }}')" @else wire:navigate @endif
                class="flex flex-col items-center justify-center w-full h-full min-h-[48px] relative {{ request()->routeIs('community.*') ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }} {{ !Route::has('community.index') ? 'opacity-60' : '' }}"
            >
                <div class="relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    @if($communityCount > 0 && Route::has('community.index'))
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 text-white text-xs rounded-full flex items-center justify-center">{{ $communityCount }}</span>
                    @endif
                </div>
                <span class="text-xs mt-1">{{ __('助け合い') }}</span>
            </a>
            
            {{-- Events --}}
            <a 
                href="{{ Route::has('events.index') ? route('events.index') : '#' }}" 
                @if(!Route::has('events.index')) onclick="event.preventDefault(); alert('{{ __('この機能は近日公開予定です') }}')" @else wire:navigate @endif
                class="flex flex-col items-center justify-center w-full h-full min-h-[48px] {{ request()->routeIs('events.*') ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }} {{ !Route::has('events.index') ? 'opacity-60' : '' }}"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="text-xs mt-1">{{ __('イベント') }}</span>
            </a>
            
            {{-- More Menu --}}
            <button 
                @click="moreOpen = !moreOpen"
                class="flex flex-col items-center justify-center w-full h-full min-h-[48px] text-gray-500 dark:text-gray-400"
                :class="moreOpen ? 'text-green-600 dark:text-green-400' : ''"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                <span class="text-xs mt-1">{{ __('その他') }}</span>
            </button>
        </div>
        
        {{-- More Menu Overlay --}}
        <div 
            x-show="moreOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="moreOpen = false"
            class="fixed inset-0 bg-black/50 z-40"
            style="bottom: 64px;"
        ></div>
        
        {{-- More Menu Panel --}}
        <div 
            x-show="moreOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            @click.away="moreOpen = false"
            class="fixed left-0 right-0 bottom-16 bg-white dark:bg-gray-800 rounded-t-2xl shadow-2xl z-50 max-h-[70vh] overflow-y-auto"
        >
            {{-- Drag Handle --}}
            <div class="flex justify-center py-2">
                <div class="w-10 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
            </div>
            
            <div class="px-4 pb-6 space-y-2">
                {{-- Knowledge Base --}}
                <a 
                    href="{{ Route::has('knowledge.index') ? route('knowledge.index') : '#' }}" 
                    @if(!Route::has('knowledge.index')) onclick="event.preventDefault(); alert('{{ __('この機能は近日公開予定です') }}')" @else wire:navigate @endif
                    @click="moreOpen = false"
                    class="flex items-center px-4 py-4 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition min-h-[48px] {{ !Route::has('knowledge.index') ? 'opacity-60' : '' }}"
                >
                    <svg class="w-6 h-6 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    {{ __('知識倉庫') }}
                    @if(!Route::has('knowledge.index'))
                        <span class="ml-auto text-xs text-gray-400">{{ __('準備中') }}</span>
                    @endif
                </a>
                
                {{-- Profile --}}
                <a 
                    href="{{ route('profile.edit') }}" 
                    wire:navigate
                    @click="moreOpen = false"
                    class="flex items-center px-4 py-4 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition min-h-[48px]"
                >
                    <svg class="w-6 h-6 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    {{ __('マイプロフィール') }}
                </a>
                
                {{-- News (in More menu for Reijikai users) --}}
                @if(auth()->user()?->hasAnyRole(['reijikai', 'admin']))
                    <a 
                        href="{{ Route::has('news.index') ? route('news.index') : '#' }}" 
                        @if(!Route::has('news.index')) onclick="event.preventDefault(); alert('{{ __('この機能は近日公開予定です') }}')" @else wire:navigate @endif
                        @click="moreOpen = false"
                        class="flex items-center px-4 py-4 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition min-h-[48px] {{ !Route::has('news.index') ? 'opacity-60' : '' }}"
                    >
                        <svg class="w-6 h-6 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                        {{ __('ニュース') }}
                        @if($newsCount > 0 && Route::has('news.index'))
                            <span class="ml-auto px-2 py-0.5 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 text-xs rounded-full">{{ $newsCount }}</span>
                        @endif
                        @if(!Route::has('news.index'))
                            <span class="ml-auto text-xs text-gray-400">{{ __('準備中') }}</span>
                        @endif
                    </a>
                @endif
                
                {{-- Content Management - Shokuin Only --}}
                @if(auth()->user()?->hasAnyRole(['shokuin', 'admin']))
                    <a 
                        href="{{ Route::has('content.index') ? route('content.index') : '#' }}" 
                        @if(!Route::has('content.index')) onclick="event.preventDefault(); alert('{{ __('この機能は近日公開予定です') }}')" @else wire:navigate @endif
                        @click="moreOpen = false"
                        class="flex items-center px-4 py-4 text-blue-700 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-xl transition min-h-[48px] {{ !Route::has('content.index') ? 'opacity-60' : '' }}"
                    >
                        <svg class="w-6 h-6 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        {{ __('コンテンツ管理') }}
                        @if($pendingContentCount > 0 && Route::has('content.index'))
                            <span class="ml-auto px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs rounded-full">{{ $pendingContentCount }}</span>
                        @endif
                        @if(!Route::has('content.index'))
                            <span class="ml-auto text-xs text-gray-400">{{ __('準備中') }}</span>
                        @endif
                    </a>
                @endif
                
                {{-- Admin Dashboard - Admin Only --}}
                @if(auth()->user()?->hasRole('admin'))
                    <a 
                        href="{{ route('admin.dashboard') }}" 
                        wire:navigate
                        @click="moreOpen = false"
                        class="flex items-center px-4 py-4 text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-xl transition min-h-[48px]"
                    >
                        <svg class="w-6 h-6 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <span class="font-semibold">{{ __('管理者ダッシュボード') }}</span>
                    </a>
                @endif
                
                {{-- Divider --}}
                <div class="my-2 border-t border-gray-200 dark:border-gray-700"></div>
                
                {{-- Shop (External Link) --}}
                <a 
                    href="https://shop.seikatsuclub.coop" 
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center px-4 py-4 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition min-h-[48px]"
                >
                    <svg class="w-6 h-6 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    {{ __('商品注文') }}
                    <svg class="w-4 h-4 ml-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                </a>
                
                {{-- Logout --}}
                <button 
                    wire:click="logout"
                    class="w-full flex items-center px-4 py-4 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition min-h-[48px]"
                >
                    <svg class="w-6 h-6 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    {{ __('ログアウト') }}
                </button>
            </div>
        </div>
    </nav>
</div>