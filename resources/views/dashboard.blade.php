<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Welcome Card --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-2">
                        {{ __('Welcome back, :name!', ['name' => Auth::user()->name]) }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ __("Here's what's happening in your community.") }}
                    </p>
                </div>
            </div>

            {{-- Quick Stats Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                {{-- Active Decisions Card --}}
                <a href="{{ route('decisions.index', ['tab' => 'active']) }}" 
                   class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900 rounded-lg p-3">
                                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Active Decisions') }}</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ \App\Models\Proposal::active()->count() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </a>

                {{-- Needs Your Vote Card --}}
                <a href="{{ route('decisions.index', ['tab' => 'needs_vote']) }}" 
                   class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-amber-100 dark:bg-amber-900 rounded-lg p-3">
                                <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Needs Your Vote') }}</p>
                                @php
                                    $needsVoteCount = \App\Models\Proposal::voting()
                                        ->whereDoesntHave('votes', fn($q) => $q->where('user_id', Auth::id()))
                                        ->count();
                                @endphp
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $needsVoteCount }}
                                </p>
                            </div>
                        </div>
                        @if($needsVoteCount > 0)
                            <div class="mt-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-800 dark:text-amber-100">
                                    {{ __('Action needed') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </a>

                {{-- Your Drafts Card --}}
                <a href="{{ route('decisions.index', ['tab' => 'drafts']) }}" 
                   class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-gray-100 dark:bg-gray-700 rounded-lg p-3">
                                <svg class="h-6 w-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Your Drafts') }}</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ \App\Models\Proposal::draft()->where('author_id', Auth::id())->count() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Recent Decisions --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Recent Decisions') }}
                        </h3>
                        <a href="{{ route('decisions.index') }}" 
                           class="text-sm text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300">
                            {{ __('View all') }} →
                        </a>
                    </div>

                    @php
                        $recentProposals = \App\Models\Proposal::with('author')
                            ->where(function($q) {
                                $q->where('author_id', Auth::id())
                                  ->orWhere('current_stage', '!=', 'draft');
                            })
                            ->latest('updated_at')
                            ->take(5)
                            ->get();
                    @endphp

                    @if($recentProposals->count() > 0)
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($recentProposals as $proposal)
                                <a href="{{ route('decisions.show', $proposal) }}" 
                                   class="block py-4 hover:bg-gray-50 dark:hover:bg-gray-700 -mx-6 px-6 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center min-w-0">
                                            {{-- Decision Type Icon --}}
                                            <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center
                                                        bg-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-100 
                                                        dark:bg-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-900">
                                                @switch($proposal->decision_type_config['icon'] ?? 'users')
                                                    @case('users')
                                                        <svg class="w-5 h-5 text-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-600 dark:text-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                                        </svg>
                                                        @break
                                                    @case('user-group')
                                                        <svg class="w-5 h-5 text-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-600 dark:text-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                        </svg>
                                                        @break
                                                    @case('shield-check')
                                                        <svg class="w-5 h-5 text-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-600 dark:text-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                        </svg>
                                                        @break
                                                    @default
                                                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                @endswitch
                                            </div>
                                            <div class="ml-4 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                    {{ $proposal->title }}
                                                </p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ __('by :author', ['author' => $proposal->author->name ?? 'Unknown']) }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            {{-- Stage Badge --}}
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                         bg-{{ $proposal->stage_config['color'] ?? 'gray' }}-100 
                                                         text-{{ $proposal->stage_config['color'] ?? 'gray' }}-800
                                                         dark:bg-{{ $proposal->stage_config['color'] ?? 'gray' }}-800 
                                                         dark:text-{{ $proposal->stage_config['color'] ?? 'gray' }}-100">
                                                {{ $proposal->stage_config['name'] ?? ucfirst($proposal->current_stage) }}
                                            </span>
                                            
                                            {{-- Outcome Badge (if closed) --}}
                                            @if($proposal->outcome)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                             bg-{{ $proposal->outcome_config['color'] ?? 'gray' }}-100 
                                                             text-{{ $proposal->outcome_config['color'] ?? 'gray' }}-800
                                                             dark:bg-{{ $proposal->outcome_config['color'] ?? 'gray' }}-800 
                                                             dark:text-{{ $proposal->outcome_config['color'] ?? 'gray' }}-100">
                                                    {{ $proposal->outcome_config['name'] ?? ucfirst($proposal->outcome) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('No decisions yet') }}</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Get started by creating a new proposal.') }}</p>
                            @can('create', \App\Models\Proposal::class)
                                <div class="mt-6">
                                    <a href="{{ route('decisions.create') }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        {{ __('New Proposal') }}
                                    </a>
                                </div>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            @can('create', \App\Models\Proposal::class)
                <div class="bg-gradient-to-r from-green-600 to-green-700 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-white">{{ __('Ready to start a new discussion?') }}</h3>
                                <p class="mt-1 text-green-100">{{ __('Create a proposal and gather feedback from the community.') }}</p>
                            </div>
                            <a href="{{ route('decisions.create') }}" 
                               class="inline-flex items-center px-4 py-2 border border-white text-sm font-medium rounded-md text-white hover:bg-white hover:text-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-green-600 focus:ring-white transition-colors">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('Create Proposal') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endcan

        </div>
    </div>
</x-app-layout>
