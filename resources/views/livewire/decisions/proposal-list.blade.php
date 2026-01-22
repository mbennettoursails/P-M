<div class="min-h-screen bg-gray-50">
    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-3xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-bold text-gray-900">Decisions</h1>
                
                @can('create', App\Models\Proposal::class)
                    <a href="{{ route('decisions.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                        <x-heroicon-o-plus class="w-5 h-5 mr-1" />
                        New
                    </a>
                @endcan
            </div>
        </div>
    </header>

    {{-- Tabs --}}
    <div class="bg-white border-b border-gray-200 sticky top-[73px] z-10">
        <div class="max-w-3xl mx-auto">
            <nav class="flex overflow-x-auto scrollbar-hide -mb-px" aria-label="Tabs">
                @foreach($tabs as $key => $tab)
                    <button wire:click="setTab('{{ $key }}')"
                            class="flex-shrink-0 px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                                   {{ $activeTab === $key 
                                      ? 'border-green-600 text-green-600' 
                                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        {{ $tab['label'] }}
                        @if(($tabCounts[$key] ?? 0) > 0)
                            <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full 
                                         {{ $activeTab === $key ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $tabCounts[$key] }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </nav>
        </div>
    </div>

    {{-- Search --}}
    <div class="max-w-3xl mx-auto px-4 py-3">
        <div class="relative">
            <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input type="text" 
                   wire:model.live.debounce.300ms="search"
                   placeholder="Search proposals..."
                   class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
        </div>
    </div>

    {{-- Proposals List --}}
    <div class="max-w-3xl mx-auto px-4 pb-24">
        @forelse($proposals as $proposal)
            <a href="{{ route('decisions.show', $proposal) }}"
               class="block bg-white rounded-xl border border-gray-200 p-4 mb-3 hover:border-gray-300 hover:shadow-sm transition-all">
                
                {{-- Header Row --}}
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        {{-- Decision Type Icon --}}
                        <div class="w-8 h-8 rounded-lg bg-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-100 
                                    text-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-600 
                                    flex items-center justify-center">
                            <x-dynamic-component :component="'heroicon-o-' . ($proposal->decision_type_config['icon'] ?? 'question-mark-circle')" 
                                                 class="w-4 h-4" />
                        </div>
                        
                        {{-- Title --}}
                        <h3 class="font-medium text-gray-900 line-clamp-1">{{ $proposal->title }}</h3>
                    </div>
                </div>

                {{-- Stage & Meta Row --}}
                <div class="flex items-center flex-wrap gap-2 mb-3">
                    {{-- Stage Badge --}}
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                 bg-{{ $proposal->stage_config['color'] ?? 'gray' }}-100 
                                 text-{{ $proposal->stage_config['color'] ?? 'gray' }}-700">
                        <x-dynamic-component :component="'heroicon-o-' . ($proposal->stage_config['icon'] ?? 'question-mark-circle')" 
                                             class="w-3 h-3 mr-1" />
                        {{ $proposal->stage_config['name'] ?? ucfirst($proposal->current_stage) }}
                    </span>

                    {{-- Time Remaining --}}
                    @if($proposal->time_remaining)
                        <span class="inline-flex items-center text-xs {{ $proposal->is_overdue ? 'text-red-600' : 'text-gray-500' }}">
                            <x-heroicon-o-clock class="w-3.5 h-3.5 mr-1" />
                            {{ $proposal->time_remaining }}
                        </span>
                    @endif

                    {{-- Outcome Badge --}}
                    @if($proposal->outcome)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                     bg-{{ $proposal->outcome_config['color'] ?? 'gray' }}-100 
                                     text-{{ $proposal->outcome_config['color'] ?? 'gray' }}-700">
                            <x-dynamic-component :component="'heroicon-s-' . ($proposal->outcome_config['icon'] ?? 'question-mark-circle')" 
                                                 class="w-3 h-3 mr-1" />
                            {{ $proposal->outcome_config['name'] ?? ucfirst($proposal->outcome) }}
                        </span>
                    @endif
                </div>

                {{-- Author --}}
                <div class="text-sm text-gray-500 mb-3">
                    By {{ $proposal->author->name ?? 'Unknown' }}
                </div>

                {{-- Progress Bar (for voting stage) --}}
                @if($proposal->is_voting)
                    <div class="space-y-1">
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-green-500 rounded-full transition-all duration-300"
                                 style="width: {{ $proposal->quorum_met ? 100 : min(($proposal->total_votes / max($proposal->eligible_voters_count, 1)) * 100, 100) }}%">
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>{{ $proposal->total_votes }}/{{ $proposal->eligible_voters_count }} voted</span>
                            @if($proposal->quorum_met)
                                <span class="text-green-600 font-medium">Quorum met</span>
                            @else
                                <span>{{ $proposal->votes_needed_for_quorum }} more needed</span>
                            @endif
                        </div>
                    </div>
                @endif
            </a>
        @empty
            <div class="text-center py-12">
                <x-heroicon-o-document-text class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                <h3 class="text-gray-500 font-medium mb-1">No proposals yet</h3>
                <p class="text-gray-400 text-sm">
                    @if($activeTab === 'drafts')
                        Create your first proposal to get started.
                    @else
                        No proposals match the current filter.
                    @endif
                </p>
            </div>
        @endforelse

        {{-- Pagination --}}
        @if($proposals->hasPages())
            <div class="mt-6">
                {{ $proposals->links() }}
            </div>
        @endif
    </div>
</div>
