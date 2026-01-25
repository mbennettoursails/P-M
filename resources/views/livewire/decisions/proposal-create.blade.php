<div class="min-h-screen bg-gray-50 pb-32"
     x-data="{ 
         debugMode: true,
         logAction(action, data = {}) {
             if (this.debugMode) {
                 console.log(`[ProposalCreate] ${action}`, data);
             }
         }
     }"
     x-init="logAction('Component initialized', { step: {{ $step }}, isSubmitting: {{ $isSubmitting ? 'true' : 'false' }} })">
    
    {{-- Debug Panel (Remove in production) --}}
    <div class="fixed top-20 right-4 z-50 bg-black/80 text-white text-xs p-3 rounded-lg max-w-xs" x-show="debugMode">
        <div class="font-bold mb-2">🐛 Debug Info</div>
        <div>Step: <span class="text-green-400">{{ $step }}</span></div>
        <div>isSubmitting: <span class="text-yellow-400">{{ $isSubmitting ? 'true' : 'false' }}</span></div>
        <div>Title: <span class="text-blue-400">{{ Str::limit($title, 20) ?: '(empty)' }}</span></div>
        <div>Decision: <span class="text-purple-400">{{ $decision_type }}</span></div>
        <div class="mt-2">
            <button @click="debugMode = false" class="text-red-400 hover:text-red-300">Hide</button>
        </div>
    </div>

    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('decisions.index') }}" class="text-gray-500 hover:text-gray-700 p-1" wire:navigate>
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </a>
                <h1 class="text-lg font-semibold text-gray-900">New Proposal</h1>
                <button wire:click="saveDraft" 
                        wire:loading.attr="disabled" 
                        wire:target="saveDraft"
                        @click="logAction('Save Draft clicked')"
                        class="text-sm text-gray-500 hover:text-gray-700 disabled:opacity-50">
                    <span wire:loading.remove wire:target="saveDraft">Save Draft</span>
                    <span wire:loading wire:target="saveDraft">Saving...</span>
                </button>
            </div>
        </div>
    </header>

    {{-- Step Indicator --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                @foreach($this->steps as $stepNum => $stepInfo)
                    <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
                        <div class="flex flex-col items-center">
                            <button type="button" wire:click="goToStep({{ $stepNum }})"
                                    @click="logAction('Step indicator clicked', { targetStep: {{ $stepNum }} })"
                                    @if($stepNum >= $step) disabled @endif
                                    class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium transition-colors
                                           {{ $step === $stepNum ? 'bg-green-600 text-white' : '' }}
                                           {{ $step > $stepNum ? 'bg-green-100 text-green-600 hover:bg-green-200 cursor-pointer' : '' }}
                                           {{ $step < $stepNum ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : '' }}">
                                @if($step > $stepNum)
                                    <x-heroicon-s-check class="w-5 h-5" />
                                @else
                                    {{ $stepNum }}
                                @endif
                            </button>
                            <span class="text-xs mt-1 text-gray-500 hidden sm:block text-center whitespace-nowrap">{{ $stepInfo['title'] }}</span>
                        </div>
                        @if(!$loop->last)
                            <div class="flex-1 h-0.5 mx-2 {{ $step > $stepNum ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Form Content --}}
    <div class="max-w-4xl mx-auto px-4 py-6">

        {{-- ══════════════════════════════════════════════════════════════
             STEP 1: Basic Information
             ══════════════════════════════════════════════════════════════ --}}
        @if($step === 1)
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Basic Information</h2>
                <p class="text-sm text-gray-500 mb-6">Enter the title and description of your proposal</p>

                <div class="space-y-6">
                    {{-- Title Field --}}
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="title"
                               wire:model.blur="title"
                               maxlength="255"
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-transparent transition-shadow
                                      @error('title') border-red-300 focus:ring-red-500 @enderror"
                               placeholder="Enter a clear, descriptive title">
                        @error('title')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 mt-1">Be specific and action-oriented</p>
                    </div>

                    {{-- Description Field --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea id="description"
                                  wire:model.blur="description"
                                  rows="12"
                                  maxlength="10000"
                                  class="w-full border border-gray-300 rounded-lg px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-transparent transition-shadow resize-y min-h-[250px]
                                         @error('description') border-red-300 focus:ring-red-500 @enderror"
                                  placeholder="Provide detailed information about your proposal...

Include:
• Background and context
• What you're proposing
• Expected outcomes
• Any resources needed"></textarea>
                        @error('description')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 mt-1">Plain text formatting</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════
             STEP 2: Decision Settings
             ══════════════════════════════════════════════════════════════ --}}
        @if($step === 2)
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Decision Settings</h2>
                <p class="text-sm text-gray-500 mb-6">Choose how participants will vote on this proposal</p>

                <div class="space-y-6">
                    {{-- Decision Type Selection --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Decision Type <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-3">
                            @foreach($this->decisionTypes as $key => $type)
                                <button type="button" 
                                        wire:click="selectDecisionType('{{ $key }}')"
                                        @click="logAction('Decision type selected', { type: '{{ $key }}' })"
                                        class="w-full text-left p-4 rounded-xl border-2 transition-all
                                               {{ $decision_type === $key
                                                  ? 'border-green-500 bg-green-50 ring-1 ring-green-500'
                                                  : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">
                                    <div class="flex items-start">
                                        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0
                                                    {{ $decision_type === $key
                                                       ? 'bg-' . $type['color'] . '-100 text-' . $type['color'] . '-600'
                                                       : 'bg-gray-100 text-gray-500' }}">
                                            <x-dynamic-component :component="'heroicon-o-' . $type['icon']" class="w-6 h-6" />
                                        </div>
                                        <div class="ml-4 flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <p class="font-semibold text-gray-900">{{ $type['name'] }}</p>
                                                @if($decision_type === $key)
                                                    <x-heroicon-s-check-circle class="w-6 h-6 text-green-500 flex-shrink-0" />
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1">{{ $type['short_description'] }}</p>
                                        </div>
                                    </div>

                                    @if($decision_type === $key)
                                        <div class="mt-4 pt-4 border-t border-green-200 space-y-3">
                                            <p class="text-sm text-gray-700">{{ $type['description'] }}</p>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                                <div class="bg-white/70 rounded-lg p-3">
                                                    <p class="font-medium text-gray-700 mb-1">✓ Best for</p>
                                                    <p class="text-gray-600 text-xs sm:text-sm">{{ $type['best_for'] }}</p>
                                                </div>
                                                <div class="bg-white/70 rounded-lg p-3">
                                                    <p class="font-medium text-gray-700 mb-1">💡 Examples</p>
                                                    <p class="text-gray-600 text-xs sm:text-sm">{{ $type['examples'] }}</p>
                                                </div>
                                            </div>
                                            <div class="bg-amber-50 rounded-lg p-3">
                                                <p class="text-sm text-amber-800">
                                                    <strong>Note:</strong> {{ $type['considerations'] }}
                                                </p>
                                            </div>
                                            <div class="pt-2">
                                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Vote Options</p>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($type['vote_labels'] as $index => $label)
                                                        @php $voteKey = $type['votes'][$index]; @endphp
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                                                     bg-{{ $type['vote_colors'][$voteKey] }}-100 text-{{ $type['vote_colors'][$voteKey] }}-700">
                                                            {{ $label }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Quorum Percentage --}}
                    <div class="pt-4 border-t border-gray-100"
                         x-data="{ quorum: @entangle('quorum_percentage').live }">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quorum Percentage</label>
                        <p class="text-xs text-gray-500 mb-3">Minimum participation required for a valid vote</p>
                        <div class="flex items-center gap-4">
                            <div class="flex-1 relative">
                                <input type="range" 
                                       x-model="quorum"
                                       @change="logAction('Quorum changed', { value: quorum })"
                                       min="25" max="100" step="5"
                                       class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-green-600">
                                <div class="flex justify-between text-xs text-gray-400 mt-2 px-0.5">
                                    <span>25%</span>
                                    <span>50%</span>
                                    <span>75%</span>
                                    <span>100%</span>
                                </div>
                            </div>
                            <div class="w-20 text-right">
                                <span class="text-2xl font-bold text-gray-900 tabular-nums" x-text="quorum + '%'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Pass Threshold (only for Democratic) --}}
                    @if($decision_type === 'democratic')
                        <div class="pt-4 border-t border-gray-100"
                             x-data="{ threshold: @entangle('pass_threshold').live }">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pass Threshold</label>
                            <p class="text-xs text-gray-500 mb-3">Percentage of "Yes" votes needed to pass</p>
                            <div class="flex items-center gap-4">
                                <div class="flex-1 relative">
                                    <input type="range" 
                                           x-model="threshold"
                                           @change="logAction('Threshold changed', { value: threshold })"
                                           min="50" max="100" step="5"
                                           class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-green-600">
                                    <div class="flex justify-between text-xs text-gray-400 mt-2 px-0.5">
                                        <span>50%</span>
                                        <span>67%</span>
                                        <span>75%</span>
                                        <span>100%</span>
                                    </div>
                                </div>
                                <div class="w-20 text-right">
                                    <span class="text-2xl font-bold text-gray-900 tabular-nums" x-text="threshold + '%'"></span>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Voting Options --}}
                    <div class="space-y-4 pt-4 border-t border-gray-100">
                        <p class="text-sm font-medium text-gray-700">Voting Options</p>

                        <label class="flex items-center justify-between cursor-pointer p-3 rounded-lg hover:bg-gray-50 -mx-3">
                            <div class="flex-1 pr-4">
                                <p class="font-medium text-gray-900">Anonymous Voting</p>
                                <p class="text-sm text-gray-500">Hide voter identities from other participants</p>
                            </div>
                            <button type="button" wire:click="$toggle('allow_anonymous_voting')" role="switch"
                                    class="relative w-11 h-6 rounded-full transition-colors flex-shrink-0 {{ $allow_anonymous_voting ? 'bg-green-600' : 'bg-gray-200' }}">
                                <span class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform {{ $allow_anonymous_voting ? 'translate-x-5' : '' }}"></span>
                            </button>
                        </label>

                        <label class="flex items-center justify-between cursor-pointer p-3 rounded-lg hover:bg-gray-50 -mx-3">
                            <div class="flex-1 pr-4">
                                <p class="font-medium text-gray-900">Show Results During Voting</p>
                                <p class="text-sm text-gray-500">Display vote counts before voting ends</p>
                            </div>
                            <button type="button" wire:click="$toggle('show_results_during_voting')" role="switch"
                                    class="relative w-11 h-6 rounded-full transition-colors flex-shrink-0 {{ $show_results_during_voting ? 'bg-green-600' : 'bg-gray-200' }}">
                                <span class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform {{ $show_results_during_voting ? 'translate-x-5' : '' }}"></span>
                            </button>
                        </label>
                    </div>
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════
             STEP 3: Participants
             ══════════════════════════════════════════════════════════════ --}}
        @if($step === 3)
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Participants</h2>
                <p class="text-sm text-gray-500 mb-6">Set who can view and participate in this proposal</p>

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Allowed Roles <span class="text-red-500">*</span></label>
                        <div class="space-y-2">
                            @php
                                $roleConfig = [
                                    'reijikai' => ['label' => 'Committee Members', 'japanese' => '委員会', 'icon' => 'user-group', 'color' => 'purple'],
                                    'shokuin' => ['label' => 'Staff', 'japanese' => '職員', 'icon' => 'briefcase', 'color' => 'indigo'],
                                    'volunteer' => ['label' => 'Volunteers', 'japanese' => 'ボランティア', 'icon' => 'heart', 'color' => 'cyan'],
                                ];
                            @endphp

                            @foreach($roleConfig as $role => $config)
                                <button type="button" wire:click="toggleRole('{{ $role }}')"
                                        @click="logAction('Role toggled', { role: '{{ $role }}' })"
                                        class="w-full flex items-center p-4 rounded-xl border-2 transition-all text-left
                                               {{ in_array($role, $allowed_roles) ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0
                                                {{ in_array($role, $allowed_roles) ? 'bg-'.$config['color'].'-100 text-'.$config['color'].'-600' : 'bg-gray-100 text-gray-400' }}">
                                        <x-dynamic-component :component="'heroicon-o-' . $config['icon']" class="w-5 h-5" />
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <span class="font-medium text-gray-900">{{ $config['label'] }}</span>
                                        <span class="ml-2 text-xs text-gray-500">({{ $config['japanese'] }})</span>
                                    </div>
                                    <div class="flex-shrink-0 ml-4">
                                        @if(in_array($role, $allowed_roles))
                                            <x-heroicon-s-check-circle class="w-6 h-6 text-green-500" />
                                        @else
                                            <div class="w-6 h-6 rounded-full border-2 border-gray-300"></div>
                                        @endif
                                    </div>
                                </button>
                            @endforeach
                        </div>
                        @error('allowed_roles')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <label class="flex items-center justify-between cursor-pointer p-3 rounded-lg hover:bg-gray-50 -mx-3">
                            <div class="flex-1 pr-4">
                                <p class="font-medium text-gray-900">Invite Only</p>
                                <p class="text-sm text-gray-500">Only specifically invited users can participate</p>
                            </div>
                            <button type="button" wire:click="toggleInviteOnly" role="switch"
                                    class="relative w-11 h-6 rounded-full transition-colors flex-shrink-0 {{ $is_invite_only ? 'bg-green-600' : 'bg-gray-200' }}">
                                <span class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform {{ $is_invite_only ? 'translate-x-5' : '' }}"></span>
                            </button>
                        </label>
                    </div>

                    @if($is_invite_only)
                        <div class="pt-4 border-t border-gray-100 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Invite Participants <span class="text-red-500">*</span></label>
                                <p class="text-xs text-gray-500 mb-3">Search and select specific users to invite.</p>

                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400" />
                                    </div>
                                    <input type="text" wire:model.live.debounce.500ms="user_search"
                                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg text-base focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           placeholder="Search by name or email...">
                                    <div wire:loading wire:target="user_search" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <svg class="animate-spin h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </div>
                                </div>

                                @if($searchedUsers->count() > 0)
                                    <div class="mt-2 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                        @foreach($searchedUsers as $user)
                                            <button type="button" wire:click="addInvitedUser({{ $user->id }})"
                                                    class="w-full flex items-center p-3 hover:bg-gray-50 text-left border-b border-gray-100 last:border-0">
                                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-gray-600">
                                                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                                </div>
                                                <div class="ml-3 flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                                                    <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                                                </div>
                                                <x-heroicon-o-plus-circle class="w-5 h-5 text-green-500 ml-2 flex-shrink-0" />
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif($showUserSearch && strlen($user_search) >= 2)
                                    <div class="mt-2 p-4 bg-gray-50 rounded-lg text-center" wire:loading.remove wire:target="user_search">
                                        <p class="text-sm text-gray-500">No users found matching "{{ $user_search }}"</p>
                                    </div>
                                @endif
                            </div>

                            @if($this->invitedUsers->count() > 0)
                                <div>
                                    <p class="text-sm font-medium text-gray-700 mb-2">Invited ({{ $this->invitedUsers->count() }})</p>
                                    <div class="space-y-2">
                                        @foreach($this->invitedUsers as $user)
                                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                                                <div class="flex items-center min-w-0">
                                                    <div class="w-8 h-8 rounded-full bg-green-200 flex items-center justify-center text-sm font-medium text-green-700">
                                                        {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                                    </div>
                                                    <div class="ml-3 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                                                        <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                                                    </div>
                                                </div>
                                                <button type="button" wire:click="removeInvitedUser({{ $user->id }})"
                                                        class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-full transition-colors">
                                                    <x-heroicon-o-x-mark class="w-5 h-5" />
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                                    <p class="text-sm text-amber-700">⚠️ Please invite at least one user to participate.</p>
                                </div>
                            @endif
                            @error('invited_user_ids')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════
             STEP 4: Timeline & Documents
             ══════════════════════════════════════════════════════════════ --}}
        @if($step === 4)
            <div class="space-y-6">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-1">Timeline</h2>
                    <p class="text-sm text-gray-500 mb-6">Set deadlines for feedback and voting (optional)</p>

                    <div class="space-y-4">
                        <div>
                            <label for="feedback_deadline" class="block text-sm font-medium text-gray-700 mb-1">Feedback Deadline</label>
                            <input type="datetime-local" id="feedback_deadline" wire:model.blur="feedback_deadline"
                                   min="{{ now()->format('Y-m-d\TH:i') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            @error('feedback_deadline')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="voting_deadline" class="block text-sm font-medium text-gray-700 mb-1">Voting Deadline</label>
                            <input type="datetime-local" id="voting_deadline" wire:model.blur="voting_deadline"
                                   min="{{ $feedback_deadline ?: now()->format('Y-m-d\TH:i') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            @error('voting_deadline')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-1">Documents</h2>
                    <p class="text-sm text-gray-500 mb-6">Attach supporting files (optional)</p>

                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-green-400 hover:bg-green-50 transition-colors">
                        <div wire:loading.remove wire:target="documents">
                            <x-heroicon-o-arrow-up-tray class="w-8 h-8 text-gray-400 mb-2 mx-auto" />
                            <span class="text-sm text-gray-500">Click to upload files</span>
                            <span class="text-xs text-gray-400 mt-1 block">PDF, DOC, XLS, PPT, images up to 10MB</span>
                        </div>
                        <div wire:loading wire:target="documents" class="text-center">
                            <svg class="animate-spin h-8 w-8 text-green-500 mb-2 mx-auto" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span class="text-sm text-gray-500">Uploading...</span>
                        </div>
                        <input type="file" wire:model="documents" multiple class="hidden"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.webp">
                    </label>

                    @if(count($documents) > 0)
                        <div class="mt-4 space-y-2">
                            @foreach($documents as $index => $doc)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex items-center min-w-0">
                                        <x-heroicon-o-document class="w-5 h-5 text-gray-500 flex-shrink-0" />
                                        <span class="ml-2 text-sm text-gray-700 truncate">{{ $doc->getClientOriginalName() }}</span>
                                        <span class="ml-2 text-xs text-gray-400">({{ number_format($doc->getSize() / 1024, 0) }} KB)</span>
                                    </div>
                                    <button type="button" wire:click="removeDocument({{ $index }})"
                                            class="p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors">
                                        <x-heroicon-o-x-mark class="w-5 h-5" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @error('documents.*')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                </div>

                {{-- Summary --}}
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl border border-green-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Proposal Summary</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Title</dt><dd class="text-gray-900 font-medium text-right max-w-[60%] truncate">{{ $title ?: '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Decision Type</dt><dd class="text-gray-900">{{ $this->selectedDecisionType['name'] ?? '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Quorum</dt><dd class="text-gray-900">{{ $quorum_percentage }}%</dd></div>
                        @if($decision_type === 'democratic')
                            <div class="flex justify-between"><dt class="text-gray-500">Pass Threshold</dt><dd class="text-gray-900">{{ $pass_threshold }}%</dd></div>
                        @endif
                        <div class="flex justify-between"><dt class="text-gray-500">Participants</dt><dd class="text-gray-900">{{ $is_invite_only ? count($invited_user_ids) . ' invited' : count($allowed_roles) . ' role(s)' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Documents</dt><dd class="text-gray-900">{{ count($documents) }} file(s)</dd></div>
                    </dl>
                </div>
            </div>
        @endif

        @error('submit')
            <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-700">❌ {{ $message }}</p>
            </div>
        @enderror
    </div>

    {{-- Bottom Navigation --}}
    <div class="fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 p-4 z-20">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            @if($step > 1)
                <button type="button" 
                        wire:click="previousStep" 
                        wire:loading.attr="disabled"
                        @click="logAction('Back button clicked', { currentStep: {{ $step }} })"
                        class="px-6 py-3 text-gray-600 font-medium hover:bg-gray-100 rounded-xl transition-colors disabled:opacity-50">
                    ← Back
                </button>
            @else
                <div></div>
            @endif

            @if($step < $totalSteps)
                <button type="button" 
                        wire:click="nextStep" 
                        wire:loading.attr="disabled" 
                        wire:target="nextStep"
                        @click="logAction('Next button clicked', { currentStep: {{ $step }} })"
                        class="px-6 py-3 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 transition-colors disabled:opacity-50 flex items-center">
                    <span wire:loading.remove wire:target="nextStep">Next →</span>
                    <span wire:loading wire:target="nextStep" class="flex items-center">
                        <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Processing...
                    </span>
                </button>
            @else
                {{-- Simplified button for debugging --}}
                <button type="button" 
                        wire:click.prevent="submit"
                        x-on:click="
                            console.log('=== SUBMIT BUTTON CLICKED ===');
                            console.log('isSubmitting:', {{ $isSubmitting ? 'true' : 'false' }});
                            console.log('Livewire component:', $wire);
                            console.log('Calling $wire.submit()...');
                            $wire.submit().then(() => {
                                console.log('Submit promise resolved');
                            }).catch((err) => {
                                console.error('Submit promise rejected:', err);
                            });
                        "
                        class="px-6 py-3 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 transition-colors flex items-center">
                    ✓ Create Proposal
                </button>
                
                {{-- Alternative: Direct form submission button for testing --}}
                <button type="button"
                        onclick="
                            console.log('Direct onclick - calling Livewire.dispatch');
                            const component = Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                            console.log('Found component:', component);
                            if (component) {
                                component.call('submit');
                            }
                        "
                        class="ml-2 px-4 py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 text-sm">
                    🔧 Test Submit
                </button>
            @endif
        </div>
    </div>
    
    {{-- Livewire Debug Script --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            console.log('[ProposalCreate] Livewire initialized');
            
            Livewire.hook('request', ({ component, commit, respond, succeed, fail }) => {
                console.log('[ProposalCreate] Request started', { 
                    componentId: component.id,
                    calls: commit.calls 
                });
                
                succeed(({ snapshot, effect }) => {
                    console.log('[ProposalCreate] Request succeeded', { effect });
                });
                
                fail(({ error }) => {
                    console.error('[ProposalCreate] Request FAILED', { error });
                });
            });
        });
    </script>
</div>