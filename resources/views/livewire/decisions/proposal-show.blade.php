<div class="min-h-screen bg-gray-50 pb-20">
    {{-- Header --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            {{-- Breadcrumb --}}
            <nav class="flex items-center text-sm text-gray-500 mb-4">
                <a href="{{ route('decisions.index') }}" class="hover:text-coop-600">{{ __('decisions.title') }}</a>
                <x-heroicon-o-chevron-right class="w-4 h-4 mx-2" />
                <span class="text-gray-900">{{ Str::limit($proposal->localized_title, 30) }}</span>
            </nav>

            {{-- Title & Stage --}}
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ $proposal->localized_title }}
                        </h1>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                     bg-{{ $proposal->stage_config['color'] }}-100 text-{{ $proposal->stage_config['color'] }}-800">
                            <x-dynamic-component :component="'heroicon-o-' . $proposal->stage_config['icon']" class="w-4 h-4 mr-1.5" />
                            {{ $proposal->stage_config['name_ja'] }}
                        </span>
                    </div>
                    
                    {{-- Meta --}}
                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-500">
                        <span class="flex items-center">
                            <x-heroicon-o-user class="w-4 h-4 mr-1" />
                            {{ $proposal->author->name }}
                        </span>
                        <span class="flex items-center">
                            <x-heroicon-o-scale class="w-4 h-4 mr-1" />
                            {{ $proposal->decision_type_config['name_ja'] }}
                        </span>
                        @if($proposal->time_remaining)
                            <span class="flex items-center text-amber-600">
                                <x-heroicon-o-clock class="w-4 h-4 mr-1" />
                                {{ $proposal->time_remaining }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2">
                    @if($canEdit)
                        <a href="{{ route('decisions.edit', $proposal->uuid) }}"
                           class="inline-flex items-center px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                            <x-heroicon-o-pencil class="w-4 h-4 mr-1.5" />
                            {{ __('decisions.actions.edit') }}
                        </a>
                    @endif
                    
                    @if($canAdvanceStage && count($availableTransitions) > 0)
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open"
                                    class="inline-flex items-center px-3 py-2 text-sm text-white bg-coop-600 rounded-lg hover:bg-coop-700">
                                {{ __('decisions.show.stage_actions') }}
                                <x-heroicon-o-chevron-down class="w-4 h-4 ml-1.5" />
                            </button>
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                                @foreach($availableTransitions as $stage)
                                    <button wire:click="openStageModal('{{ $stage }}')"
                                            class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 first:rounded-t-lg last:rounded-b-lg">
                                        {{ __('decisions.show.advance_to', ['stage' => __('decisions.stages.' . $stage)]) }}
                                    </button>
                                @endforeach
                                
                                @if($proposal->current_stage === 'voting')
                                    <hr class="my-1">
                                    <button wire:click="closeVoting"
                                            wire:confirm="{{ __('decisions.show.confirm_close') }}"
                                            class="w-full px-4 py-2 text-left text-sm text-amber-700 hover:bg-amber-50">
                                        {{ __('decisions.actions.close_voting') }}
                                    </button>
                                @endif

                                @if($canWithdraw)
                                    <hr class="my-1">
                                    <button wire:click="openWithdrawModal"
                                            class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 rounded-b-lg">
                                        {{ __('decisions.actions.withdraw') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Outcome Banner --}}
            @if($proposal->outcome)
                <div class="mt-4 p-4 rounded-lg bg-{{ $proposal->outcome_config['color'] }}-50 border border-{{ $proposal->outcome_config['color'] }}-200">
                    <div class="flex items-center">
                        <x-dynamic-component :component="'heroicon-o-' . $proposal->outcome_config['icon']" 
                                             class="w-6 h-6 text-{{ $proposal->outcome_config['color'] }}-600 mr-3" />
                        <div>
                            <p class="font-medium text-{{ $proposal->outcome_config['color'] }}-800">
                                {{ __('decisions.results.outcome') }}: {{ $proposal->outcome_config['name_ja'] }}
                            </p>
                            @if($proposal->outcome_summary)
                                <p class="text-sm text-{{ $proposal->outcome_config['color'] }}-700 mt-1">
                                    {{ $proposal->outcome_summary }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Tabs - Mobile Scrollable --}}
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex space-x-1 overflow-x-auto py-2" aria-label="Tabs">
                @foreach($tabs as $key => $tab)
                    @if(!isset($tab['show']) || $tab['show'])
                        <button wire:click="setTab('{{ $key }}')"
                                class="flex items-center px-4 py-2 text-sm font-medium rounded-lg whitespace-nowrap transition-colors
                                       {{ $activeTab === $key 
                                          ? 'bg-coop-100 text-coop-700' 
                                          : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                            <x-dynamic-component :component="'heroicon-o-' . $tab['icon']" class="w-4 h-4 mr-1.5" />
                            {{ $tab['label'] }}
                            @if(isset($tab['count']) && $tab['count'] > 0)
                                <span class="ml-2 px-2 py-0.5 text-xs rounded-full 
                                             {{ $activeTab === $key ? 'bg-coop-200 text-coop-800' : 'bg-gray-200 text-gray-600' }}">
                                    {{ $tab['count'] }}
                                </span>
                            @endif
                        </button>
                    @endif
                @endforeach
            </nav>
        </div>
    </div>

    {{-- Content --}}
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Overview Tab --}}
                @if($activeTab === 'overview')
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('decisions.show.tabs.overview') }}</h2>
                        <div class="prose prose-sm max-w-none text-gray-700">
                            {!! nl2br(e($proposal->localized_description)) !!}
                        </div>
                    </div>
                @endif

                {{-- Discussion Tab --}}
                @if($activeTab === 'discussion')
                    <livewire:decisions.components.comment-thread :proposal="$proposal" />
                @endif

                {{-- Vote Tab --}}
                @if($activeTab === 'vote')
                    <div class="space-y-6">
                        {{-- Voting Widget --}}
                        @if($proposal->current_stage === 'voting' || $userVote)
                            <livewire:decisions.components.voting-widget :proposal="$proposal" />
                        @endif

                        {{-- Results Chart --}}
                        <livewire:decisions.components.results-chart :proposal="$proposal" />
                    </div>
                @endif

                {{-- Documents Tab --}}
                @if($activeTab === 'documents')
                    <livewire:decisions.components.document-list :proposal="$proposal" />
                @endif

                {{-- Participants Tab --}}
                @if($activeTab === 'participants')
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ __('decisions.participants.title') }} ({{ $statistics['participants'] }})
                            </h2>
                            @if($canAdvanceStage)
                                <button wire:click="openInviteModal"
                                        class="text-sm text-coop-600 hover:text-coop-700">
                                    <x-heroicon-o-user-plus class="w-4 h-4 inline mr-1" />
                                    {{ __('decisions.participants.invite') }}
                                </button>
                            @endif
                        </div>

                        <div class="space-y-2">
                            @foreach($proposal->participants as $participant)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-coop-100 flex items-center justify-center text-coop-700 font-medium">
                                            {{ mb_substr($participant->name, 0, 1) }}
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $participant->name }}
                                                @if($participant->id === $proposal->author_id)
                                                    <span class="text-xs text-gray-500">({{ __('decisions.labels.author') }})</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500">{{ $participant->role }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($proposal->hasUserVoted($participant))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-green-100 text-green-700">
                                                {{ __('decisions.participants.voted') }}
                                            </span>
                                        @elseif($proposal->current_stage === 'voting')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">
                                                {{ __('decisions.participants.not_voted') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Send Reminders --}}
                        @if($canAdvanceStage && $proposal->current_stage === 'voting')
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <button wire:click="sendReminders"
                                        class="text-sm text-amber-600 hover:text-amber-700">
                                    <x-heroicon-o-bell class="w-4 h-4 inline mr-1" />
                                    {{ __('decisions.actions.send_reminders') }}
                                </button>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- History Tab --}}
                @if($activeTab === 'history')
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('decisions.history.title') }}</h2>
                        
                        <div class="relative">
                            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                            <div class="space-y-6">
                                @foreach($proposal->stages->reverse() as $stage)
                                    <div class="relative flex items-start pl-10">
                                        <div class="absolute left-2 w-4 h-4 rounded-full 
                                                    {{ $stage->is_active ? 'bg-coop-500 ring-4 ring-coop-100' : 'bg-gray-300' }}">
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <span class="font-medium text-gray-900">
                                                    {{ $stage->stage_config['name_ja'] ?? $stage->stage_type }}
                                                </span>
                                                <span class="text-sm text-gray-500">
                                                    {{ $stage->started_at->format('Y/m/d H:i') }}
                                                </span>
                                            </div>
                                            @if($stage->transitioner)
                                                <p class="text-sm text-gray-500 mt-1">
                                                    {{ $stage->transitioner->name }}が変更
                                                </p>
                                            @endif
                                            @if($stage->notes)
                                                <p class="text-sm text-gray-600 mt-2 p-2 bg-gray-50 rounded">
                                                    {{ $stage->notes }}
                                                </p>
                                            @endif
                                            @if(!$stage->is_active && $stage->ended_at)
                                                <p class="text-xs text-gray-400 mt-1">
                                                    {{ __('decisions.history.duration', ['duration' => $stage->duration_formatted]) }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Statistics Card --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">統計</h3>
                    
                    <dl class="space-y-4">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('decisions.labels.participants') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $statistics['participants'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('decisions.labels.votes_cast') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $statistics['votes_cast'] }} / {{ $statistics['voters'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('decisions.labels.quorum') }}</dt>
                            <dd class="text-sm font-medium {{ $statistics['quorum_reached'] ? 'text-green-600' : 'text-gray-900' }}">
                                {{ $statistics['vote_percentage'] }}% / {{ $statistics['quorum_percentage'] }}%
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('decisions.labels.comments') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $statistics['comments'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">{{ __('decisions.labels.documents') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $statistics['documents'] }}</dd>
                        </div>
                    </dl>

                    {{-- Vote Progress --}}
                    @if(in_array($proposal->current_stage, ['voting', 'closed']))
                        <div class="mt-6 pt-4 border-t border-gray-100">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-500">投票進捗</span>
                                <span class="font-medium {{ $statistics['quorum_reached'] ? 'text-green-600' : 'text-gray-900' }}">
                                    {{ $statistics['vote_percentage'] }}%
                                </span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all
                                            {{ $statistics['quorum_reached'] ? 'bg-green-500' : 'bg-coop-500' }}"
                                     style="width: {{ min($statistics['vote_percentage'], 100) }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-400 mt-1">
                                <span>0%</span>
                                <span class="text-amber-500">{{ $statistics['quorum_percentage'] }}% 定足数</span>
                                <span>100%</span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Deadlines Card --}}
                @if($proposal->feedback_deadline || $proposal->voting_deadline)
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">期限</h3>
                        <dl class="space-y-3">
                            @if($proposal->feedback_deadline)
                                <div>
                                    <dt class="text-xs text-gray-400">{{ __('decisions.labels.feedback_deadline') }}</dt>
                                    <dd class="text-sm font-medium text-gray-900">
                                        {{ $proposal->feedback_deadline->format('Y/m/d H:i') }}
                                    </dd>
                                </div>
                            @endif
                            @if($proposal->voting_deadline)
                                <div>
                                    <dt class="text-xs text-gray-400">{{ __('decisions.labels.voting_deadline') }}</dt>
                                    <dd class="text-sm font-medium {{ $proposal->voting_deadline->isPast() ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ $proposal->voting_deadline->format('Y/m/d H:i') }}
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif

                {{-- Quick Actions Card --}}
                @if($canVote || $canComment)
                    <div class="bg-coop-50 rounded-xl p-6 border border-coop-100">
                        <h3 class="text-sm font-medium text-coop-800 mb-3">アクション</h3>
                        <div class="space-y-2">
                            @if($canVote && !$userVote)
                                <button wire:click="setTab('vote')"
                                        class="w-full px-4 py-2 bg-coop-600 text-white rounded-lg hover:bg-coop-700 text-sm">
                                    <x-heroicon-o-hand-raised class="w-4 h-4 inline mr-1" />
                                    {{ __('decisions.actions.submit_vote') }}
                                </button>
                            @endif
                            @if($canComment)
                                <button wire:click="setTab('discussion')"
                                        class="w-full px-4 py-2 bg-white text-coop-700 border border-coop-300 rounded-lg hover:bg-coop-50 text-sm">
                                    <x-heroicon-o-chat-bubble-left class="w-4 h-4 inline mr-1" />
                                    {{ __('decisions.actions.add_comment') }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Stage Transition Modal --}}
    @if($showStageModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeStageModal"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('decisions.show.advance_to', ['stage' => __('decisions.stages.' . $targetStage)]) }}
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">{{ __('decisions.show.confirm_advance') }}</p>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('decisions.show.notes_placeholder') }}
                        </label>
                        <textarea wire:model="stageNotes"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button wire:click="closeStageModal"
                                class="px-4 py-2 text-gray-700 hover:text-gray-900">
                            {{ __('decisions.actions.cancel') }}
                        </button>
                        <button wire:click="advanceStage"
                                class="px-4 py-2 bg-coop-600 text-white rounded-lg hover:bg-coop-700">
                            確認
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
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('decisions.actions.withdraw') }}
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">{{ __('decisions.show.confirm_withdraw') }}</p>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('decisions.show.withdraw_reason_placeholder') }}
                        </label>
                        <textarea wire:model="withdrawReason"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button wire:click="closeWithdrawModal"
                                class="px-4 py-2 text-gray-700 hover:text-gray-900">
                            {{ __('decisions.actions.cancel') }}
                        </button>
                        <button wire:click="withdraw"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            {{ __('decisions.actions.withdraw') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Invite Modal --}}
    @if($showInviteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeInviteModal"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('decisions.participants.invite') }}
                    </h3>
                    
                    {{-- Search --}}
                    <div class="relative mb-4">
                        <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                        <input type="text" 
                               wire:model.live.debounce.300ms="inviteSearch"
                               placeholder="{{ __('decisions.participants.search_placeholder') }}"
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500">
                    </div>

                    {{-- User List --}}
                    <div class="border border-gray-200 rounded-lg max-h-60 overflow-y-auto mb-4">
                        @forelse($invitableUsers as $user)
                            <label class="flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0">
                                <input type="checkbox" 
                                       wire:click="toggleInviteUser({{ $user->id }})"
                                       {{ in_array($user->id, $selectedInviteUsers) ? 'checked' : '' }}
                                       class="rounded text-coop-600 focus:ring-coop-500">
                                <div class="ml-3">
                                    <span class="block text-sm font-medium text-gray-900">{{ $user->name }}</span>
                                    <span class="block text-xs text-gray-500">{{ $user->email }}</span>
                                </div>
                            </label>
                        @empty
                            <p class="p-4 text-sm text-gray-500 text-center">ユーザーが見つかりません</p>
                        @endforelse
                    </div>

                    <div class="flex justify-end gap-3">
                        <button wire:click="closeInviteModal"
                                class="px-4 py-2 text-gray-700 hover:text-gray-900">
                            {{ __('decisions.actions.cancel') }}
                        </button>
                        <button wire:click="inviteUsers"
                                class="px-4 py-2 bg-coop-600 text-white rounded-lg hover:bg-coop-700"
                                {{ empty($selectedInviteUsers) ? 'disabled' : '' }}>
                            {{ __('decisions.actions.invite') }} ({{ count($selectedInviteUsers) }})
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
