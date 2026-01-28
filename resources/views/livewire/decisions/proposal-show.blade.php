{{--
    ╔══════════════════════════════════════════════════════════════════════╗
    ║  PROPOSAL SHOW - View Proposal Details                               ║
    ║  North Tokyo COOP Hub                                                ║
    ╚══════════════════════════════════════════════════════════════════════╝
--}}

<div class="min-h-screen bg-gray-50 pb-24" wire:key="proposal-show-{{ $proposal->id }}">
    
    {{-- ════════════════════════════════════════════════════════════════
         HEADER
         ════════════════════════════════════════════════════════════════ --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-20">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('decisions.index') }}" 
                   wire:navigate
                   class="text-gray-500 hover:text-gray-700 p-1 rounded-lg hover:bg-gray-100 transition-colors">
                    <x-heroicon-o-arrow-left class="w-6 h-6" />
                </a>
                
                @if($isAuthor)
                    {{-- Author Actions Menu - Using Livewire state instead of Alpine --}}
                    <div class="relative">
                        <button type="button" 
                                wire:click="$toggle('showActionsMenu')"
                                class="p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100">
                            <x-heroicon-o-ellipsis-vertical class="w-5 h-5" />
                        </button>
                        
                        @if($showActionsMenu ?? false)
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-30"
                                 wire:click.away="$set('showActionsMenu', false)">
                                @if($canEdit)
                                    <a href="{{ route('decisions.edit', $proposal) }}"
                                       wire:navigate
                                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <x-heroicon-o-pencil class="w-4 h-4 mr-2" />
                                        {{ __('Edit') }}
                                    </a>
                                @endif
                                
                                @if(!empty($availableTransitions))
                                    <div class="border-t border-gray-100 my-1"></div>
                                    @foreach($availableTransitions as $transition)
                                        <button type="button"
                                                wire:click="openStageModal('{{ $transition }}')"
                                                class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <x-heroicon-o-arrow-right class="w-4 h-4 mr-2" />
                                            {{ __('Move to') }} {{ __(ucfirst($transition)) }}
                                        </button>
                                    @endforeach
                                @endif
                                
                                @can('withdraw', $proposal)
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <button type="button"
                                            wire:click="openWithdrawModal"
                                            class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <x-heroicon-o-x-circle class="w-4 h-4 mr-2" />
                                        {{ __('Withdraw') }}
                                    </button>
                                @endcan
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </header>

    {{-- ════════════════════════════════════════════════════════════════
         PROPOSAL HEADER
         ════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-6">
            {{-- Title --}}
            <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $proposal->title }}</h1>
            
            {{-- Stage Progress --}}
            @include('components.decisions.stage-progress', ['proposal' => $proposal])
            
            {{-- Meta Badges --}}
            <div class="flex items-center flex-wrap gap-2 mt-4">
                {{-- Decision Type --}}
                @php
                    $typeConfig = $proposal->decision_type_config;
                    $typeColor = $typeConfig['color'] ?? 'gray';
                @endphp
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-{{ $typeColor }}-100 text-{{ $typeColor }}-700">
                    <x-dynamic-component :component="'heroicon-o-' . ($typeConfig['icon'] ?? 'question-mark-circle')" class="w-4 h-4 mr-1.5" />
                    {{ $typeConfig['name'] ?? ucfirst($proposal->decision_type) }}
                </span>

                {{-- Stage --}}
                @php
                    $stageConfig = $proposal->stage_config;
                    $stageColor = $stageConfig['color'] ?? 'gray';
                @endphp
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-{{ $stageColor }}-100 text-{{ $stageColor }}-700">
                    <x-dynamic-component :component="'heroicon-o-' . ($stageConfig['icon'] ?? 'question-mark-circle')" class="w-4 h-4 mr-1.5" />
                    {{ $stageConfig['name'] ?? ucfirst($proposal->current_stage) }}
                </span>

                {{-- Time Remaining --}}
                @if($proposal->time_remaining)
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium {{ $proposal->is_overdue ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700' }}">
                        <x-heroicon-o-clock class="w-4 h-4 mr-1.5" />
                        {{ $proposal->time_remaining }}
                    </span>
                @endif

                {{-- Outcome --}}
                @if($proposal->outcome)
                    @php
                        $outcomeConfig = $proposal->outcome_config;
                        $outcomeColor = $outcomeConfig['color'] ?? 'gray';
                    @endphp
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-{{ $outcomeColor }}-100 text-{{ $outcomeColor }}-700">
                        <x-dynamic-component :component="'heroicon-s-' . ($outcomeConfig['icon'] ?? 'check-circle')" class="w-4 h-4 mr-1.5" />
                        {{ $outcomeConfig['name'] ?? ucfirst($proposal->outcome) }}
                    </span>
                @endif
            </div>

            {{-- Author & Stats --}}
            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-medium">
                        {{ strtoupper(substr($proposal->author->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $proposal->author->name ?? __('Unknown') }}</p>
                        <p class="text-xs text-gray-500">{{ __('Created') }} {{ $proposal->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                
                @if($proposal->is_voting)
                    <div class="text-right">
                        <p class="text-lg font-bold text-gray-900">{{ $proposal->total_votes }}/{{ $proposal->eligible_voters_count }}</p>
                        <p class="text-xs text-gray-500">{{ __('votes cast') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         TAB NAVIGATION
         ════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white border-b border-gray-200 sticky top-[73px] z-10">
        <div class="max-w-4xl mx-auto">
            <nav class="flex overflow-x-auto scrollbar-hide -mb-px" wire:key="tab-navigation">
                <button type="button"
                        wire:click="setTab('overview')"
                        class="flex-shrink-0 px-5 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                               {{ $activeTab === 'overview' ? 'border-green-600 text-green-600 bg-green-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                    <x-heroicon-o-document-text class="w-4 h-4 inline mr-1.5" />
                    {{ __('Overview') }}
                </button>
                
                <button type="button"
                        wire:click="setTab('discussion')"
                        class="flex-shrink-0 px-5 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                               {{ $activeTab === 'discussion' ? 'border-green-600 text-green-600 bg-green-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                    <x-heroicon-o-chat-bubble-left-right class="w-4 h-4 inline mr-1.5" />
                    {{ __('Discussion') }}
                    @if($commentsCount > 0)
                        <span class="ml-1.5 px-1.5 py-0.5 bg-gray-200 text-gray-600 text-xs rounded-full">{{ $commentsCount }}</span>
                    @endif
                </button>
                
                @if($proposal->is_voting || $proposal->is_closed)
                    <button type="button"
                            wire:click="setTab('vote')"
                            class="flex-shrink-0 px-5 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                                   {{ $activeTab === 'vote' ? 'border-green-600 text-green-600 bg-green-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                        <x-heroicon-o-hand-raised class="w-4 h-4 inline mr-1.5" />
                        {{ __('Vote') }}
                        @if($canVote && !$userVote)
                            <span class="ml-1.5 w-2 h-2 bg-amber-500 rounded-full inline-block animate-pulse"></span>
                        @endif
                    </button>
                @endif
                
                <button type="button"
                        wire:click="setTab('documents')"
                        class="flex-shrink-0 px-5 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                               {{ $activeTab === 'documents' ? 'border-green-600 text-green-600 bg-green-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                    <x-heroicon-o-paper-clip class="w-4 h-4 inline mr-1.5" />
                    {{ __('Documents') }}
                    @if($documentsCount > 0)
                        <span class="ml-1.5 px-1.5 py-0.5 bg-gray-200 text-gray-600 text-xs rounded-full">{{ $documentsCount }}</span>
                    @endif
                </button>
                
                <button type="button"
                        wire:click="setTab('history')"
                        class="flex-shrink-0 px-5 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                               {{ $activeTab === 'history' ? 'border-green-600 text-green-600 bg-green-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                    <x-heroicon-o-clock class="w-4 h-4 inline mr-1.5" />
                    {{ __('History') }}
                </button>
            </nav>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         TAB CONTENT
         ════════════════════════════════════════════════════════════════ --}}
    <div class="max-w-4xl mx-auto px-4 py-6" wire:key="tab-content-{{ $activeTab }}">
        
        {{-- Overview Tab --}}
        @if($activeTab === 'overview')
            <div class="space-y-6">
                {{-- Description Card --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-slate-50">
                        <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                            <x-heroicon-o-document-text class="w-5 h-5 text-gray-500" />
                            {{ __('Description') }}
                            <span class="text-gray-400 text-sm font-normal">説明</span>
                        </h2>
                    </div>
                    <div class="p-6">
                        {{-- Formatted Description with proper typography --}}
                        <div class="prose prose-sm prose-gray max-w-none">
                            {!! nl2br(e($proposal->description)) !!}
                        </div>
                    </div>
                </div>

                {{-- Decision Settings Card --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                            <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-gray-500" />
                            {{ __('Decision Settings') }}
                            <span class="text-gray-400 text-sm font-normal">決定設定</span>
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('Quorum') }}</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $proposal->quorum_percentage }}%</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ __('minimum participation') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('Pass Threshold') }}</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $proposal->pass_threshold }}%</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ __('votes needed to pass') }}</p>
                            </div>
                            @if($proposal->voting_deadline)
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('Voting Deadline') }}</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $proposal->voting_deadline->format('M j, Y g:i A') }}</p>
                                </div>
                            @endif
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('Anonymous Voting') }}</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $proposal->allow_anonymous_voting ? __('Enabled') : __('Not allowed') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Participants Card --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                            <x-heroicon-o-users class="w-5 h-5 text-gray-500" />
                            {{ __('Participants') }}
                            <span class="text-gray-400 text-sm font-normal">参加者</span>
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-wrap gap-2">
                            @foreach($proposal->allowed_roles ?? ['reijikai'] as $role)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-indigo-100 text-indigo-700">
                                    <x-heroicon-s-user-group class="w-4 h-4 mr-1.5" />
                                    {{ ucfirst($role) }}
                                </span>
                            @endforeach
                        </div>
                        @if($proposal->is_invite_only)
                            <p class="text-sm text-amber-600 mt-3 flex items-center gap-1">
                                <x-heroicon-s-lock-closed class="w-4 h-4" />
                                {{ __('Invite-only proposal') }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Vote Results Preview (if voting or closed) --}}
                @if($proposal->is_voting || $proposal->is_closed)
                    @if($proposal->show_results_during_voting || $proposal->is_closed)
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100">
                                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                    <x-heroicon-o-chart-bar class="w-5 h-5 text-gray-500" />
                                    {{ __('Current Results') }}
                                </h3>
                            </div>
                            <div class="p-6">
                                @php $results = $proposal->vote_results; @endphp
                                <div class="space-y-3">
                                    @foreach($results as $vote => $data)
                                        <div>
                                            <div class="flex justify-between text-sm mb-1">
                                                <span class="font-medium text-gray-700">{{ $data['label'] }}</span>
                                                <span class="text-gray-500">{{ $data['count'] }} ({{ $data['percentage'] }}%)</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                <div class="bg-{{ $data['color'] }}-500 h-2.5 rounded-full transition-all duration-500" 
                                                     style="width: {{ $data['percentage'] }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                {{-- Quorum Status --}}
                                <div class="mt-4 pt-4 border-t border-gray-100">
                                    <div class="flex items-center gap-2">
                                        @if($proposal->quorum_met)
                                            <x-heroicon-s-check-circle class="w-5 h-5 text-green-500" />
                                            <span class="text-sm text-green-700 font-medium">{{ __('Quorum reached') }}</span>
                                        @else
                                            <x-heroicon-s-exclamation-circle class="w-5 h-5 text-amber-500" />
                                            <span class="text-sm text-amber-700 font-medium">
                                                {{ __('Need') }} {{ $proposal->votes_needed_for_quorum }} {{ __('more votes for quorum') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
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
                    <div class="flex items-center p-4 bg-white rounded-xl border border-gray-200 hover:border-gray-300 transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-{{ $document->color }}-100 text-{{ $document->color }}-600 flex items-center justify-center flex-shrink-0">
                            <x-dynamic-component :component="'heroicon-o-' . $document->icon" class="w-6 h-6" />
                        </div>
                        <div class="ml-4 flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $document->title }}</p>
                            <p class="text-xs text-gray-500">
                                {{ strtoupper($document->extension ?? 'FILE') }} • 
                                {{ $document->file_size_formatted }}
                            </p>
                        </div>
                        <a href="{{ $document->download_url }}" 
                           target="_blank"
                           class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors">
                            <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                        </a>
                    </div>
                @empty
                    <div class="text-center py-12 bg-white rounded-2xl border border-gray-200">
                        <x-heroicon-o-document class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                        <p class="text-gray-500 font-medium">{{ __('No documents attached') }}</p>
                        <p class="text-sm text-gray-400 mt-1">{{ __('Documents will appear here once uploaded') }}</p>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- History Tab --}}
        @if($activeTab === 'history')
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="divide-y divide-gray-100">
                    @forelse($stageHistory as $stage)
                        @php
                            $stageConf = \App\Models\Proposal::STAGES[$stage->stage_type] ?? [];
                            $stageCol = $stageConf['color'] ?? 'gray';
                        @endphp
                        <div class="flex items-start gap-4 p-4">
                            <div class="w-10 h-10 rounded-full bg-{{ $stageCol }}-100 text-{{ $stageCol }}-600 flex items-center justify-center flex-shrink-0">
                                <x-dynamic-component :component="'heroicon-o-' . ($stageConf['icon'] ?? 'question-mark-circle')" class="w-5 h-5" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $stageConf['name'] ?? ucfirst($stage->stage_type) }}
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $stage->started_at->format('M j, Y g:i A') }}
                                    @if($stage->transitioner)
                                        • {{ __('by') }} {{ $stage->transitioner->name }}
                                    @endif
                                </p>
                                @if($stage->notes)
                                    <p class="text-sm text-gray-600 mt-2 p-3 bg-gray-50 rounded-lg">{{ $stage->notes }}</p>
                                @endif
                            </div>
                            @if($stage->is_active)
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                    {{ __('Current') }}
                                </span>
                            @endif
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            {{ __('No history available') }}
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         STAGE TRANSITION MODAL
         ════════════════════════════════════════════════════════════════ --}}
    @if($showStageModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50 transition-opacity" wire:click="closeStageModal"></div>
                
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('Move to') }} {{ __(ucfirst($targetStage)) }}?
                    </h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Notes') }} ({{ __('optional') }})</label>
                        <textarea wire:model="stageNotes"
                                  rows="3"
                                  class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                  placeholder="{{ __('Add any notes about this transition...') }}"></textarea>
                    </div>
                    
                    @error('stage')
                        <p class="text-sm text-red-600 mb-4 flex items-center gap-1">
                            <x-heroicon-s-exclamation-circle class="w-4 h-4" />
                            {{ $message }}
                        </p>
                    @enderror
                    
                    <div class="flex justify-end gap-3">
                        <button type="button"
                                wire:click="closeStageModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-xl transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button"
                                wire:click="confirmAdvanceStage"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50"
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-xl transition-colors">
                            <span wire:loading.remove wire:target="confirmAdvanceStage">{{ __('Confirm') }}</span>
                            <span wire:loading wire:target="confirmAdvanceStage">{{ __('Processing...') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════
         WITHDRAW MODAL
         ════════════════════════════════════════════════════════════════ --}}
    @if($showWithdrawModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50 transition-opacity" wire:click="closeWithdrawModal"></div>
                
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Withdraw Proposal?') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('This action cannot be undone.') }}</p>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Reason') }} ({{ __('optional') }})</label>
                        <textarea wire:model="withdrawReason"
                                  rows="3"
                                  class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                  placeholder="{{ __('Explain why you\'re withdrawing...') }}"></textarea>
                    </div>
                    
                    @error('withdraw')
                        <p class="text-sm text-red-600 mb-4 flex items-center gap-1">
                            <x-heroicon-s-exclamation-circle class="w-4 h-4" />
                            {{ $message }}
                        </p>
                    @enderror
                    
                    <div class="flex justify-end gap-3">
                        <button type="button"
                                wire:click="closeWithdrawModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-xl transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button"
                                wire:click="confirmWithdraw"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors">
                            <span wire:loading.remove wire:target="confirmWithdraw">{{ __('Withdraw') }}</span>
                            <span wire:loading wire:target="confirmWithdraw">{{ __('Processing...') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>