<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('ダッシュボード') }}
        </h2>
    </x-slot>

    {{-- Main Content - Mobile-first padding --}}
    <div class="py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
            
            {{-- Welcome Card - Condensed on mobile --}}
            <div class="card p-4 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">
                    {{ __('おかえりなさい、:name さん！', ['name' => Auth::user()->name]) }}
                </h3>
                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">
                    {{ __('コミュニティの最新情報をご確認ください。') }}
                </p>
            </div>

            {{-- Quick Stats Grid - Horizontal scroll on mobile, grid on desktop --}}
            <div class="relative">
                {{-- Mobile: Horizontal scroll --}}
                <div class="flex lg:hidden gap-3 overflow-x-auto pb-2 scrollbar-hide scroll-smooth-touch -mx-4 px-4">
                    {{-- Active Decisions --}}
                    <a href="{{ route('decisions.index', ['tab' => 'active']) }}" 
                       class="flex-shrink-0 w-[280px] card card-hover p-4 tap-transparent">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900/50 rounded-xl p-3">
                                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('アクティブな議案') }}</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ \App\Models\Proposal::active()->count() }}
                                </p>
                            </div>
                        </div>
                    </a>

                    {{-- Needs Your Vote --}}
                    <a href="{{ route('decisions.index', ['tab' => 'needs_vote']) }}" 
                       class="flex-shrink-0 w-[280px] card card-hover p-4 tap-transparent">
                        @php
                            $needsVoteCount = \App\Models\Proposal::voting()
                                ->whereDoesntHave('votes', fn($q) => $q->where('user_id', Auth::id()))
                                ->count();
                        @endphp
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-amber-100 dark:bg-amber-900/50 rounded-xl p-3">
                                <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('投票待ち') }}</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $needsVoteCount }}</p>
                            </div>
                        </div>
                        @if($needsVoteCount > 0)
                            <div class="mt-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-800/50 dark:text-amber-200">
                                    <span class="w-1.5 h-1.5 bg-amber-500 rounded-full mr-1.5 animate-pulse"></span>
                                    {{ __('要対応') }}
                                </span>
                            </div>
                        @endif
                    </a>

                    {{-- Your Drafts --}}
                    <a href="{{ route('decisions.index', ['tab' => 'drafts']) }}" 
                       class="flex-shrink-0 w-[280px] card card-hover p-4 tap-transparent">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-gray-100 dark:bg-gray-700 rounded-xl p-3">
                                <svg class="h-6 w-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('下書き') }}</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ \App\Models\Proposal::draft()->where('author_id', Auth::id())->count() }}
                                </p>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- Desktop: Grid layout --}}
                <div class="hidden lg:grid lg:grid-cols-3 gap-6">
                    {{-- Active Decisions --}}
                    <a href="{{ route('decisions.index', ['tab' => 'active']) }}" 
                       class="card card-hover p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900/50 rounded-xl p-3">
                                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('アクティブな議案') }}</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ \App\Models\Proposal::active()->count() }}
                                </p>
                            </div>
                        </div>
                    </a>

                    {{-- Needs Your Vote --}}
                    <a href="{{ route('decisions.index', ['tab' => 'needs_vote']) }}" 
                       class="card card-hover p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-amber-100 dark:bg-amber-900/50 rounded-xl p-3">
                                <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('投票待ち') }}</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $needsVoteCount }}</p>
                            </div>
                        </div>
                        @if($needsVoteCount > 0)
                            <div class="mt-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-800/50 dark:text-amber-200">
                                    <span class="w-1.5 h-1.5 bg-amber-500 rounded-full mr-1.5 animate-pulse"></span>
                                    {{ __('要対応') }}
                                </span>
                            </div>
                        @endif
                    </a>

                    {{-- Your Drafts --}}
                    <a href="{{ route('decisions.index', ['tab' => 'drafts']) }}" 
                       class="card card-hover p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-gray-100 dark:bg-gray-700 rounded-xl p-3">
                                <svg class="h-6 w-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('下書き') }}</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ \App\Models\Proposal::draft()->where('author_id', Auth::id())->count() }}
                                </p>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- Scroll indicator for mobile --}}
                <div class="flex lg:hidden justify-center mt-2 space-x-1">
                    <div class="w-8 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
                    <div class="w-2 h-1 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                    <div class="w-2 h-1 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                </div>
            </div>

            {{-- Recent Decisions --}}
            <div class="card">
                <div class="p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('最近の議案') }}
                        </h3>
                        <a href="{{ route('decisions.index') }}" 
                           class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 touch-target flex items-center justify-center">
                            {{ __('すべて見る') }}
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    @php
                        $recentProposals = \App\Models\Proposal::with(['author'])
                            ->whereIn('current_stage', ['feedback', 'voting', 'closed'])
                            ->orderBy('updated_at', 'desc')
                            ->take(5)
                            ->get();
                    @endphp

                    @if($recentProposals->count() > 0)
                        <div class="divide-y divide-gray-100 dark:divide-gray-700 -mx-4 sm:mx-0">
                            @foreach($recentProposals as $proposal)
                                <a href="{{ route('decisions.show', $proposal) }}" 
                                   class="flex items-center justify-between p-4 sm:px-0 hover:bg-gray-50 dark:hover:bg-gray-700/50 sm:hover:bg-transparent sm:dark:hover:bg-transparent transition-colors tap-transparent touch-target">
                                    <div class="flex items-center min-w-0 flex-1">
                                        {{-- Icon --}}
                                        <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center
                                                    bg-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-100 
                                                    dark:bg-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-900/50">
                                            @switch($proposal->decision_type_config['icon'] ?? 'document')
                                                @case('users')
                                                    <svg class="w-5 h-5 text-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-600 dark:text-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                                    </svg>
                                                    @break
                                                @case('shield-check')
                                                    <svg class="w-5 h-5 text-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-600 dark:text-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                    </svg>
                                                    @break
                                                @default
                                                    <svg class="w-5 h-5 text-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-600 dark:text-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                            @endswitch
                                        </div>
                                        
                                        {{-- Content --}}
                                        <div class="ml-3 min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                {{ $proposal->title }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                {{ $proposal->author->name ?? __('不明') }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    {{-- Status Badge - Hidden on very small screens --}}
                                    <div class="hidden xs:flex items-center ml-2 flex-shrink-0">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                     bg-{{ $proposal->stage_config['color'] ?? 'gray' }}-100 
                                                     text-{{ $proposal->stage_config['color'] ?? 'gray' }}-700
                                                     dark:bg-{{ $proposal->stage_config['color'] ?? 'gray' }}-900/50 
                                                     dark:text-{{ $proposal->stage_config['color'] ?? 'gray' }}-300">
                                            {{ $proposal->stage_config['name'] ?? ucfirst($proposal->current_stage) }}
                                        </span>
                                    </div>
                                    
                                    {{-- Chevron --}}
                                    <svg class="w-5 h-5 text-gray-400 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    @else
                        {{-- Empty State --}}
                        <div class="text-center py-8 sm:py-12">
                            <div class="w-16 h-16 mx-auto bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">{{ __('議案がありません') }}</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('新しい提案を作成して始めましょう。') }}</p>
                            @can('create', \App\Models\Proposal::class)
                                <div class="mt-6">
                                    <a href="{{ route('decisions.create') }}" 
                                       class="btn btn-primary">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        {{ __('新規提案') }}
                                    </a>
                                </div>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Action CTA - Mobile-optimized --}}
            @can('create', \App\Models\Proposal::class)
                <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl sm:rounded-2xl overflow-hidden shadow-lg">
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-white">{{ __('新しい議論を始めますか？') }}</h3>
                                <p class="mt-1 text-sm text-primary-100">{{ __('提案を作成し、コミュニティからフィードバックを集めましょう。') }}</p>
                            </div>
                            <a href="{{ route('decisions.create') }}" 
                               class="btn flex-shrink-0 bg-white text-primary-600 hover:bg-primary-50 touch-target">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('提案を作成') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endcan

        </div>
    </div>
</x-app-layout>