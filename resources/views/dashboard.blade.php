<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Welcome Section --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Welcome back, :name!', ['name' => Auth::user()->name]) }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __("Here's what's happening in your community.") }}
                    </p>
                </div>
            </div>

            @php
                $hasProposalModel = class_exists(\App\Models\Proposal::class);
                $activeProposals = 0;
                $votingProposals = 0;
                $needsVote = 0;
                $myDrafts = 0;
                $proposalsNeedingVote = collect([]);
                $recentProposals = collect([]);
                
                if ($hasProposalModel) {
                    try {
                        $activeProposals = \App\Models\Proposal::whereNotIn('current_stage', ['closed', 'archived'])->count();
                        $votingProposals = \App\Models\Proposal::where('current_stage', 'voting')->count();
                        $needsVote = \App\Models\Proposal::where('current_stage', 'voting')
                            ->whereHas('participants', fn($q) => $q->where('user_id', Auth::id())->where('can_vote', true))
                            ->whereDoesntHave('votes', fn($q) => $q->where('user_id', Auth::id()))
                            ->count();
                        $myDrafts = \App\Models\Proposal::where('author_id', Auth::id())
                            ->where('current_stage', 'draft')
                            ->count();
                        $proposalsNeedingVote = \App\Models\Proposal::where('current_stage', 'voting')
                            ->whereHas('participants', fn($q) => $q->where('user_id', Auth::id())->where('can_vote', true))
                            ->whereDoesntHave('votes', fn($q) => $q->where('user_id', Auth::id()))
                            ->with('author')
                            ->limit(5)
                            ->get();
                        $recentProposals = \App\Models\Proposal::with('author')
                            ->whereNotIn('current_stage', ['draft'])
                            ->orderByDesc('updated_at')
                            ->limit(5)
                            ->get();
                    } catch (\Exception $e) {
                        // Silently handle any database errors
                    }
                }
            @endphp

            @if($hasProposalModel)
                {{-- Stats Cards --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    {{-- Active Proposals --}}
                    <a href="{{ Route::has('decisions.index') ? route('decisions.index') . '?tab=active' : '#' }}" 
                       class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $activeProposals }}</p>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">{{ __('Active Proposals') }}</p>
                            </div>
                        </div>
                    </a>

                    {{-- Voting Now --}}
                    <a href="{{ Route::has('decisions.index') ? route('decisions.index') . '?tab=voting' : '#' }}" 
                       class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-green-100 dark:bg-green-900 rounded-full">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $votingProposals }}</p>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">{{ __('Voting Now') }}</p>
                            </div>
                        </div>
                    </a>

                    {{-- Needs Your Vote --}}
                    <a href="{{ Route::has('decisions.index') ? route('decisions.index') . '?tab=needs_vote' : '#' }}" 
                       class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6 hover:shadow-md transition-shadow {{ $needsVote > 0 ? 'ring-2 ring-amber-400' : '' }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-amber-100 dark:bg-amber-900 rounded-full relative">
                                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11" />
                                </svg>
                                @if($needsVote > 0)
                                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-pulse"></span>
                                @endif
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $needsVote }}</p>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">{{ __('Needs Your Vote') }}</p>
                            </div>
                        </div>
                    </a>

                    {{-- My Drafts --}}
                    <a href="{{ Route::has('decisions.index') ? route('decisions.index') . '?tab=drafts' : '#' }}" 
                       class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-gray-100 dark:bg-gray-700 rounded-full">
                                <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $myDrafts }}</p>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">{{ __('My Drafts') }}</p>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- Main Content Grid --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Proposals Needing Your Vote --}}
                    <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    {{ __('Proposals Needing Your Vote') }}
                                </h3>
                                @if(Route::has('decisions.index'))
                                    <a href="{{ route('decisions.index') }}?tab=needs_vote" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {{ __('View All') }}
                                    </a>
                                @endif
                            </div>

                            @if($proposalsNeedingVote->isEmpty())
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        {{ __("You're all caught up! No votes pending.") }}
                                    </p>
                                </div>
                            @else
                                <div class="space-y-3">
                                    @foreach($proposalsNeedingVote as $proposal)
                                        <a href="{{ Route::has('decisions.show') ? route('decisions.show', $proposal->uuid) : '#' }}" 
                                           class="block p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                        {{ $proposal->title }}
                                                    </p>
                                                    <div class="mt-1 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                                        <span>{{ optional($proposal->author)->name ?? __('Unknown') }}</span>
                                                        <span>•</span>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-200">
                                                            {{ ucfirst($proposal->decision_type) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                @if($proposal->voting_deadline)
                                                    <span class="ml-2 text-xs text-amber-600 dark:text-amber-400 whitespace-nowrap">
                                                        {{ $proposal->voting_deadline->diffForHumans() }}
                                                    </span>
                                                @endif
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                {{ __('Quick Actions') }}
                            </h3>

                            <div class="space-y-3">
                                @if(Route::has('decisions.create'))
                                    <a href="{{ route('decisions.create') }}" 
                                       class="flex items-center p-3 bg-indigo-50 dark:bg-indigo-900/50 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900 transition-colors">
                                        <div class="flex-shrink-0 p-2 bg-indigo-500 rounded-lg">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-indigo-900 dark:text-indigo-100">{{ __('New Proposal') }}</p>
                                            <p class="text-xs text-indigo-600 dark:text-indigo-300">{{ __('Create a new decision') }}</p>
                                        </div>
                                    </a>
                                @endif

                                @if(Route::has('decisions.index'))
                                    <a href="{{ route('decisions.index') }}" 
                                       class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                        <div class="flex-shrink-0 p-2 bg-gray-500 rounded-lg">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('All Decisions') }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('View all proposals') }}</p>
                                        </div>
                                    </a>
                                @endif

                                <a href="{{ route('profile.edit') }}" 
                                   class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                    <div class="flex-shrink-0 p-2 bg-gray-500 rounded-lg">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('My Profile') }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Update your settings') }}</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recent Activity --}}
                <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Recent Proposals') }}
                            </h3>
                            @if(Route::has('decisions.index'))
                                <a href="{{ route('decisions.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                    {{ __('View All') }}
                                </a>
                            @endif
                        </div>

                        @if($recentProposals->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No proposals yet. Be the first to create one!') }}
                            </p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                {{ __('Proposal') }}
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">
                                                {{ __('Author') }}
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                {{ __('Stage') }}
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">
                                                {{ __('Updated') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($recentProposals as $proposal)
                                            @php
                                                $stageColors = [
                                                    'draft' => 'gray',
                                                    'feedback' => 'blue',
                                                    'refinement' => 'purple',
                                                    'voting' => 'amber',
                                                    'closed' => 'green',
                                                    'archived' => 'slate',
                                                ];
                                                $stageColor = $stageColors[$proposal->current_stage] ?? 'gray';
                                            @endphp
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <td class="px-4 py-4">
                                                    <a href="{{ Route::has('decisions.show') ? route('decisions.show', $proposal->uuid) : '#' }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                                        {{ Str::limit($proposal->title, 40) }}
                                                    </a>
                                                </td>
                                                <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 hidden sm:table-cell">
                                                    {{ optional($proposal->author)->name ?? __('Unknown') }}
                                                </td>
                                                <td class="px-4 py-4">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $stageColor }}-100 dark:bg-{{ $stageColor }}-900 text-{{ $stageColor }}-800 dark:text-{{ $stageColor }}-200">
                                                        {{ ucfirst($proposal->current_stage) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                                    {{ $proposal->updated_at->diffForHumans() }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                {{-- Fallback when Proposal model doesn't exist --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        {{ __("You're logged in!") }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>