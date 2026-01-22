<div class="min-h-screen bg-gray-50 pb-24">
    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-20">
        <div class="max-w-3xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('decisions.index') }}" class="text-gray-500 hover:text-gray-700">
                    <x-heroicon-o-arrow-left class="w-6 h-6" />
                </a>
                
                @if($isAuthor)
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100">
                            <x-heroicon-o-ellipsis-vertical class="w-5 h-5" />
                        </button>
                        
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-30">
                            @if($canEdit)
                                <a href="{{ route('decisions.edit', $proposal) }}"
                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <x-heroicon-o-pencil class="w-4 h-4 mr-2" />
                                    Edit
                                </a>
                            @endif
                            
                            @if(!empty($availableTransitions))
                                @foreach($availableTransitions as $transition)
                                    <button wire:click="openStageModal('{{ $transition }}')"
                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <x-heroicon-o-arrow-right class="w-4 h-4 mr-2" />
                                        Move to {{ ucfirst($transition) }}
                                    </button>
                                @endforeach
                            @endif
                            
                            @can('withdraw', $proposal)
                                <button wire:click="openWithdrawModal"
                                        class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <x-heroicon-o-x-circle class="w-4 h-4 mr-2" />
                                    Withdraw
                                </button>
                            @endcan
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </header>

    {{-- Proposal Header --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-3xl mx-auto px-4 py-6">
            {{-- Title --}}
            <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $proposal->title }}</h1>
            
            {{-- Stage Progress --}}
            @include('components.decisions.stage-progress', ['proposal' => $proposal])
            
            {{-- Meta Badges --}}
            <div class="flex items-center flex-wrap gap-2 mt-4">
                {{-- Decision Type --}}
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                             bg-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-100 
                             text-{{ $proposal->decision_type_config['color'] ?? 'gray' }}-700">
                    <x-dynamic-component :component="'heroicon-o-' . ($proposal->decision_type_config['icon'] ?? 'question-mark-circle')" 
                                         class="w-3.5 h-3.5 mr-1" />
                    {{ $proposal->decision_type_config['name'] ?? ucfirst($proposal->decision_type) }}
                </span>

                {{-- Stage --}}
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                             bg-{{ $proposal->stage_config['color'] ?? 'gray' }}-100 
                             text-{{ $proposal->stage_config['color'] ?? 'gray' }}-700">
                    <x-dynamic-component :component="'heroicon-o-' . ($proposal->stage_config['icon'] ?? 'question-mark-circle')" 
                                         class="w-3.5 h-3.5 mr-1" />
                    {{ $proposal->stage_config['name'] ?? ucfirst($proposal->current_stage) }}
                </span>

                {{-- Time Remaining --}}
                @if($proposal->time_remaining)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                 {{ $proposal->is_overdue ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700' }}">
                        <x-heroicon-o-clock class="w-3.5 h-3.5 mr-1" />
                        {{ $proposal->time_remaining }}
                    </span>
                @endif

                {{-- Outcome --}}
                @if($proposal->outcome)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                 bg-{{ $proposal->outcome_config['color'] ?? 'gray' }}-100 
                                 text-{{ $proposal->outcome_config['color'] ?? 'gray' }}-700">
                        <x-dynamic-component :component="'heroicon-s-' . ($proposal->outcome_config['icon'] ?? 'check-circle')" 
                                             class="w-3.5 h-3.5 mr-1" />
                        {{ $proposal->outcome_config['name'] ?? ucfirst($proposal->outcome) }}
                    </span>
                @endif
            </div>

            {{-- Author & Stats --}}
            <div class="flex items-center justify-between mt-4 text-sm text-gray-500">
                <span>By {{ $proposal->author->name ?? 'Unknown' }}</span>
                @if($proposal->is_voting)
                    <span>{{ $proposal->total_votes }}/{{ $proposal->eligible_voters_count }} voted</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="bg-white border-b border-gray-200 sticky top-[73px] z-10">
        <div class="max-w-3xl mx-auto">
            <nav class="flex overflow-x-auto scrollbar-hide -mb-px">
                <button wire:click="setTab('overview')"
                        class="flex-shrink-0 px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                               {{ $activeTab === 'overview' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Overview
                </button>
                
                <button wire:click="setTab('discussion')"
                        class="flex-shrink-0 px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                               {{ $activeTab === 'discussion' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Discussion
                    @if($proposal->allComments()->count() > 0)
                        <span class="ml-1 text-xs text-gray-400">({{ $proposal->allComments()->count() }})</span>
                    @endif
                </button>
                
                @if($proposal->is_voting || $proposal->is_closed)
                    <button wire:click="setTab('vote')"
                            class="flex-shrink-0 px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                                   {{ $activeTab === 'vote' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Vote
                        @if($canVote && !$userVote)
                            <span class="ml-1 w-2 h-2 bg-amber-500 rounded-full inline-block"></span>
                        @endif
                    </button>
                @endif
                
                <button wire:click="setTab('documents')"
                        class="flex-shrink-0 px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                               {{ $activeTab === 'documents' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Documents
                    @if($proposal->documents()->count() > 0)
                        <span class="ml-1 text-xs text-gray-400">({{ $proposal->documents()->count() }})</span>
                    @endif
                </button>
                
                <button wire:click="setTab('history')"
                        class="flex-shrink-0 px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                               {{ $activeTab === 'history' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    History
                </button>
            </nav>
        </div>
    </div>

    {{-- Tab Content --}}
    <div class="max-w-3xl mx-auto px-4 py-6">
        {{-- Overview Tab --}}
        @if($activeTab === 'overview')
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="prose prose-gray max-w-none">
                    {!! nl2br(e($proposal->description)) !!}
                </div>
                
                {{-- Settings Summary --}}
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Decision Settings</h3>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Quorum</dt>
                            <dd class="font-medium text-gray-900">{{ $proposal->quorum_percentage }}%</dd>
                        </div>
                        @if($proposal->decision_type === 'democratic')
                            <div>
                                <dt class="text-gray-500">Pass Threshold</dt>
                                <dd class="font-medium text-gray-900">{{ $proposal->pass_threshold }}%</dd>
                            </div>
                        @endif
                        @if($proposal->voting_deadline)
                            <div>
                                <dt class="text-gray-500">Voting Deadline</dt>
                                <dd class="font-medium text-gray-900">{{ $proposal->voting_deadline->format('M j, Y g:i A') }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-gray-500">Anonymous Voting</dt>
                            <dd class="font-medium text-gray-900">{{ $proposal->allow_anonymous_voting ? 'Allowed' : 'Not allowed' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        @endif

        {{-- Discussion Tab --}}
        @if($activeTab === 'discussion')
            <livewire:decisions.components.comment-thread :proposal="$proposal" :key="'comments-'.$proposal->id" />
        @endif

        {{-- Vote Tab --}}
        @if($activeTab === 'vote')
            <livewire:decisions.components.voting-widget :proposal="$proposal" :key="'voting-'.$proposal->id" />
        @endif

        {{-- Documents Tab --}}
        @if($activeTab === 'documents')
            <div class="space-y-3">
                @forelse($proposal->documents as $document)
                    <div class="flex items-center p-4 bg-white rounded-xl border border-gray-200">
                        <div class="w-10 h-10 rounded-lg bg-{{ $document->color }}-100 text-{{ $document->color }}-600 
                                    flex items-center justify-center">
                            <x-dynamic-component :component="'heroicon-o-' . $document->icon" class="w-5 h-5" />
                        </div>
                        <div class="ml-3 flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $document->title }}</p>
                            <p class="text-xs text-gray-500">{{ $document->file_size_formatted }}</p>
                        </div>
                        <a href="{{ $document->download_url }}" 
                           target="_blank"
                           class="p-2 text-gray-400 hover:text-gray-600">
                            <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                        </a>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <x-heroicon-o-document class="w-12 h-12 text-gray-300 mx-auto mb-2" />
                        <p>No documents attached</p>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- History Tab --}}
        @if($activeTab === 'history')
            <div class="space-y-4">
                @foreach($stageHistory as $stage)
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-full bg-{{ $stage->stage_config['color'] ?? 'gray' }}-100 
                                    text-{{ $stage->stage_config['color'] ?? 'gray' }}-600 
                                    flex items-center justify-center flex-shrink-0">
                            <x-dynamic-component :component="'heroicon-o-' . ($stage->stage_config['icon'] ?? 'question-mark-circle')" 
                                                 class="w-4 h-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $stage->stage_config['name'] ?? ucfirst($stage->stage_type) }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $stage->started_at->format('M j, Y g:i A') }}
                                @if($stage->transitioner)
                                    by {{ $stage->transitioner->name }}
                                @endif
                            </p>
                            @if($stage->notes)
                                <p class="text-sm text-gray-600 mt-1">{{ $stage->notes }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Stage Transition Modal --}}
    @if($showStageModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeStageModal"></div>
                
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Move to {{ ucfirst($targetStage) }}?
                    </h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                        <textarea wire:model="stageNotes"
                                  rows="3"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                  placeholder="Add any notes about this transition..."></textarea>
                    </div>
                    
                    @error('stage')
                        <p class="text-sm text-red-600 mb-4">{{ $message }}</p>
                    @enderror
                    
                    <div class="flex justify-end space-x-3">
                        <button wire:click="closeStageModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">
                            Cancel
                        </button>
                        <button wire:click="confirmAdvanceStage"
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg">
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Withdraw Modal --}}
    @if($showWithdrawModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeWithdrawModal"></div>
                
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Withdraw Proposal?</h3>
                    <p class="text-sm text-gray-600 mb-4">This action cannot be undone. The proposal will be archived.</p>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason (optional)</label>
                        <textarea wire:model="withdrawReason"
                                  rows="3"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                  placeholder="Explain why you're withdrawing..."></textarea>
                    </div>
                    
                    @error('withdraw')
                        <p class="text-sm text-red-600 mb-4">{{ $message }}</p>
                    @enderror
                    
                    <div class="flex justify-end space-x-3">
                        <button wire:click="closeWithdrawModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">
                            Cancel
                        </button>
                        <button wire:click="confirmWithdraw"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg">
                            Withdraw
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
