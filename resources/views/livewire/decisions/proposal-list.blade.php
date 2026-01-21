<div class="py-6 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ __('Decisions') }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Manage and participate in community decisions') }}
                </p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('decisions.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('New Proposal') }}
                </a>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="flex flex-wrap -mb-px" aria-label="Tabs">
                    @php
                        $tabs = [
                            'active' => ['label' => __('Active'), 'icon' => 'play-circle'],
                            'voting' => ['label' => __('Voting'), 'icon' => 'hand-raised'],
                            'needs_vote' => ['label' => __('Needs Vote'), 'icon' => 'exclamation-circle'],
                            'drafts' => ['label' => __('My Drafts'), 'icon' => 'pencil'],
                            'my_proposals' => ['label' => __('My Proposals'), 'icon' => 'user'],
                            'closed' => ['label' => __('Closed'), 'icon' => 'check-circle'],
                        ];
                    @endphp
                    
                    @foreach($tabs as $tabKey => $tabData)
                        <button wire:click="setTab('{{ $tabKey }}')"
                                class="flex items-center px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                                    {{ $tab === $tabKey 
                                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' 
                                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300' }}">
                            {{ $tabData['label'] }}
                            @if(isset($tabCounts[$tabKey]) && $tabCounts[$tabKey] > 0)
                                <span class="ml-2 px-2 py-0.5 text-xs rounded-full 
                                    {{ $tab === $tabKey 
                                        ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400' 
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                                    {{ $tabCounts[$tabKey] }}
                                </span>
                            @endif
                        </button>
                    @endforeach
                </nav>
            </div>

            {{-- Filters --}}
            <div class="p-4 flex flex-col sm:flex-row gap-4">
                {{-- Search --}}
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search"
                               type="text"
                               placeholder="{{ __('Search proposals...') }}"
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                {{-- Decision Type Filter --}}
                <div class="sm:w-48">
                    <select wire:model.live="decisionType"
                            class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">{{ __('All Types') }}</option>
                        <option value="democratic">{{ __('Democratic') }}</option>
                        <option value="consensus">{{ __('Consensus') }}</option>
                        <option value="consent">{{ __('Consent') }}</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Proposals List --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
            @if($proposals->isEmpty())
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('No proposals found') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if($tab === 'drafts')
                            {{ __('You have no draft proposals.') }}
                        @elseif($tab === 'needs_vote')
                            {{ __("You're all caught up! No votes pending.") }}
                        @else
                            {{ __('Get started by creating a new proposal.') }}
                        @endif
                    </p>
                    @if($tab !== 'needs_vote')
                        <div class="mt-6">
                            <a href="{{ route('decisions.create') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                {{ __('New Proposal') }}
                            </a>
                        </div>
                    @endif
                </div>
            @else
                {{-- Desktop Table View --}}
                <div class="hidden md:block">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('Proposal') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('Type') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('Stage') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('Author') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <button wire:click="sortBy('updated_at')" class="flex items-center space-x-1">
                                        <span>{{ __('Updated') }}</span>
                                        @if($sortBy === 'updated_at')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($sortDirection === 'asc')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                @endif
                                            </svg>
                                        @endif
                                    </button>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($proposals as $proposal)
                                @php
                                    $stageColors = [
                                        'draft' => 'gray',
                                        'feedback' => 'blue',
                                        'refinement' => 'purple',
                                        'voting' => 'amber',
                                        'closed' => 'green',
                                        'archived' => 'slate',
                                    ];
                                    $typeColors = [
                                        'democratic' => 'blue',
                                        'consensus' => 'purple',
                                        'consent' => 'teal',
                                    ];
                                    $stageColor = $stageColors[$proposal->current_stage] ?? 'gray';
                                    $typeColor = $typeColors[$proposal->decision_type] ?? 'gray';
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" 
                                    onclick="window.location='{{ route('decisions.show', $proposal->uuid) }}'">
                                    <td class="px-6 py-4">
                                        <div class="flex items-start">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 truncate">
                                                    {{ $proposal->title }}
                                                </p>
                                                @if($proposal->description)
                                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-1">
                                                        {{ Str::limit(strip_tags($proposal->description), 80) }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $typeColor }}-100 dark:bg-{{ $typeColor }}-900 text-{{ $typeColor }}-800 dark:text-{{ $typeColor }}-200">
                                            {{ ucfirst($proposal->decision_type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $stageColor }}-100 dark:bg-{{ $stageColor }}-900 text-{{ $stageColor }}-800 dark:text-{{ $stageColor }}-200">
                                            {{ ucfirst($proposal->current_stage) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ optional($proposal->author)->name ?? __('Unknown') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $proposal->updated_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Card View --}}
                <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($proposals as $proposal)
                        @php
                            $stageColors = [
                                'draft' => 'gray',
                                'feedback' => 'blue',
                                'refinement' => 'purple',
                                'voting' => 'amber',
                                'closed' => 'green',
                                'archived' => 'slate',
                            ];
                            $typeColors = [
                                'democratic' => 'blue',
                                'consensus' => 'purple',
                                'consent' => 'teal',
                            ];
                            $stageColor = $stageColors[$proposal->current_stage] ?? 'gray';
                            $typeColor = $typeColors[$proposal->decision_type] ?? 'gray';
                        @endphp
                        <a href="{{ route('decisions.show', $proposal->uuid) }}" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <div class="flex items-start justify-between">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                        {{ $proposal->title }}
                                    </p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $typeColor }}-100 dark:bg-{{ $typeColor }}-900 text-{{ $typeColor }}-800 dark:text-{{ $typeColor }}-200">
                                            {{ ucfirst($proposal->decision_type) }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $stageColor }}-100 dark:bg-{{ $stageColor }}-900 text-{{ $stageColor }}-800 dark:text-{{ $stageColor }}-200">
                                            {{ ucfirst($proposal->current_stage) }}
                                        </span>
                                    </div>
                                    <div class="mt-2 flex items-center text-xs text-gray-500 dark:text-gray-400">
                                        <span>{{ optional($proposal->author)->name ?? __('Unknown') }}</span>
                                        <span class="mx-2">•</span>
                                        <span>{{ $proposal->updated_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <svg class="ml-4 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($proposals->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $proposals->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>