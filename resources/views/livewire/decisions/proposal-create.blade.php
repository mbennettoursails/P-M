{{-- 
    ╔══════════════════════════════════════════════════════════════════════╗
    ║  PROPOSAL CREATE - Full Production Version                           ║
    ║  North Tokyo COOP Hub - Cooperative Decision Making Platform         ║
    ╚══════════════════════════════════════════════════════════════════════╝
    
    Key Features:
    - 4-step wizard (Basic Info → Settings → Participants → Review)
    - Three decision types: Democratic, Consensus, Consent
    - Role-based participant selection
    - Invite-only mode with user search
    - Document upload support
    - Timeline configuration
    - Bilingual (EN/JP) support
    
    Technical Notes:
    - Pure wire:click (no Alpine.js conflicts)
    - All buttons have type="button"
    - Uses Livewire 3 computed properties
--}}

<div class="min-h-screen bg-gray-50 pb-32" wire:key="proposal-create-root">
    
    {{-- ════════════════════════════════════════════════════════════════
         HEADER
         ════════════════════════════════════════════════════════════════ --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                {{-- Close Button --}}
                <a href="{{ route('decisions.index') }}" 
                   wire:navigate
                   class="text-gray-500 hover:text-gray-700 p-1 rounded-lg hover:bg-gray-100 transition-colors">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </a>
                
                {{-- Title --}}
                <h1 class="text-lg font-semibold text-gray-900">
                    {{ __('New Proposal') }}
                    <span class="text-gray-400 text-sm font-normal ml-1">新規提案</span>
                </h1>
                
                {{-- Save Draft Button --}}
                <button type="button"
                        wire:click="saveDraft"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50"
                        class="text-sm text-gray-500 hover:text-green-600 font-medium transition-colors">
                    <span wire:loading.remove wire:target="saveDraft">{{ __('Save Draft') }}</span>
                    <span wire:loading wire:target="saveDraft">{{ __('Saving...') }}</span>
                </button>
            </div>
        </div>
    </header>

    {{-- ════════════════════════════════════════════════════════════════
         STEP INDICATOR
         ════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                @foreach($stepDefinitions as $stepNum => $stepInfo)
                    <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
                        <div class="flex flex-col items-center">
                            @if($stepNum < $step)
                                {{-- Completed Step --}}
                                <button type="button"
                                        wire:click="goToStep({{ $stepNum }})"
                                        class="w-10 h-10 rounded-full flex items-center justify-center bg-green-100 text-green-600 hover:bg-green-200 transition-colors cursor-pointer">
                                    <x-heroicon-s-check class="w-6 h-6" />
                                </button>
                            @elseif($stepNum === $step)
                                {{-- Current Step --}}
                                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-green-600 text-white text-sm font-bold shadow-lg ring-4 ring-green-100">
                                    {{ $stepNum }}
                                </div>
                            @else
                                {{-- Future Step --}}
                                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-200 text-gray-500 text-sm font-medium">
                                    {{ $stepNum }}
                                </div>
                            @endif
                            
                            <span class="text-xs mt-2 font-medium text-center whitespace-nowrap hidden sm:block
                                         {{ $stepNum === $step ? 'text-green-600' : ($stepNum < $step ? 'text-green-600' : 'text-gray-400') }}">
                                {{ $stepInfo['title'] }}
                            </span>
                        </div>
                        
                        @if(!$loop->last)
                            <div class="flex-1 h-1 mx-3 rounded-full {{ $step > $stepNum ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         FORM CONTENT
         ════════════════════════════════════════════════════════════════ --}}
    <div class="max-w-4xl mx-auto px-4 py-6" wire:key="form-content-step-{{ $step }}">
        
        {{-- ════════════════════════════════════════════════════════════
             STEP 1: BASIC INFORMATION
             ════════════════════════════════════════════════════════════ --}}
        @if($step === 1)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-green-50 to-emerald-50">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                        <x-heroicon-o-document-text class="w-6 h-6 text-green-600" />
                        {{ __('Basic Information') }}
                        <span class="text-gray-400 text-base font-normal">基本情報</span>
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">{{ __('Start with a clear title and detailed description') }}</p>
                </div>

                <div class="p-6 space-y-6">
                    {{-- Title Field --}}
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('Title') }} <span class="text-red-500">*</span>
                            <span class="text-gray-400 font-normal ml-1">タイトル</span>
                        </label>
                        <input type="text"
                               id="title"
                               wire:model="title"
                               maxlength="255"
                               class="w-full border border-gray-300 rounded-xl px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-transparent transition-shadow
                                      @error('title') border-red-300 ring-2 ring-red-100 @enderror"
                               placeholder="{{ __('Enter a clear, descriptive title for your proposal') }}">
                        @error('title')
                            <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                                <x-heroicon-s-exclamation-circle class="w-4 h-4" />
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="text-xs text-gray-400 mt-2">{{ __('Be specific and action-oriented. Example: "Approve budget for community garden project"') }}</p>
                    </div>

                    {{-- Description Field --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('Description') }} <span class="text-red-500">*</span>
                            <span class="text-gray-400 font-normal ml-1">説明</span>
                        </label>
                        <textarea id="description"
                                  wire:model="description"
                                  rows="12"
                                  maxlength="10000"
                                  class="w-full border border-gray-300 rounded-xl px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-transparent transition-shadow resize-y min-h-[250px]
                                         @error('description') border-red-300 ring-2 ring-red-100 @enderror"
                                  placeholder="{{ __('Provide detailed information about your proposal...') }}

{{ __('Consider including:') }}
• {{ __('Background and context') }}
• {{ __('Specific proposal details') }}
• {{ __('Expected outcomes and benefits') }}
• {{ __('Timeline and resources needed') }}
• {{ __('Potential concerns and how to address them') }}"></textarea>
                        @error('description')
                            <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                                <x-heroicon-s-exclamation-circle class="w-4 h-4" />
                                {{ $message }}
                            </p>
                        @enderror
                        <div class="flex justify-between items-center mt-2">
                            <p class="text-xs text-gray-400">{{ __('Minimum 20 characters required') }}</p>
                            <p class="text-xs text-gray-400">{{ strlen($description) }}/10,000</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ════════════════════════════════════════════════════════════
             STEP 2: DECISION SETTINGS
             ════════════════════════════════════════════════════════════ --}}
        @if($step === 2)
            <div class="space-y-6">
                {{-- Decision Type Selection --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                        <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                            <x-heroicon-o-cog-6-tooth class="w-6 h-6 text-blue-600" />
                            {{ __('Decision Type') }}
                            <span class="text-gray-400 text-base font-normal">決定タイプ</span>
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">{{ __('Choose how this proposal will be decided') }}</p>
                    </div>

                    <div class="p-6">
                        <div class="grid gap-4">
                            @foreach($decisionTypeOptions as $type => $config)
                                <button type="button"
                                        wire:click="selectDecisionType('{{ $type }}')"
                                        class="text-left p-5 rounded-xl border-2 transition-all duration-200 group
                                               {{ $decision_type === $type 
                                                  ? 'border-green-500 bg-green-50 shadow-md' 
                                                  : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">
                                    <div class="flex items-start gap-4">
                                        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors
                                                    {{ $decision_type === $type 
                                                       ? 'bg-green-100 text-green-600' 
                                                       : 'bg-gray-100 text-gray-500 group-hover:bg-gray-200' }}">
                                            <x-dynamic-component :component="'heroicon-o-' . $config['icon']" class="w-7 h-7" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-gray-900 text-lg">
                                                {{ $config['name'] }}
                                            </h3>
                                            <p class="text-sm text-gray-400 mb-2">{{ $config['name_ja'] }}</p>
                                            <p class="text-sm text-gray-600">{{ $config['short_description'] }}</p>
                                            
                                            {{-- Vote Options Preview --}}
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @foreach($config['votes'] as $vote)
                                                    <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">
                                                        {{ ucfirst(str_replace('_', ' ', $vote)) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                        @if($decision_type === $type)
                                            <x-heroicon-s-check-circle class="w-7 h-7 text-green-500 flex-shrink-0" />
                                        @endif
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Voting Thresholds --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                            <x-heroicon-o-chart-bar class="w-5 h-5 text-gray-500" />
                            {{ __('Voting Thresholds') }}
                            <span class="text-gray-400 text-sm font-normal">投票閾値</span>
                        </h3>
                    </div>

                    <div class="p-6 space-y-6">
                        {{-- Quorum --}}
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <label class="text-sm font-medium text-gray-700">
                                    {{ __('Quorum (Minimum Participation)') }}
                                </label>
                                <span class="text-lg font-bold text-green-600">{{ $quorum_percentage }}%</span>
                            </div>
                            <input type="range" 
                                   wire:model.live="quorum_percentage" 
                                   min="25" max="100" step="5"
                                   class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-green-600">
                            <div class="flex justify-between text-xs text-gray-400 mt-1">
                                <span>25%</span>
                                <span>50%</span>
                                <span>75%</span>
                                <span>100%</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                {{ __('Percentage of eligible voters who must participate for the vote to be valid.') }}
                            </p>
                        </div>

                        {{-- Pass Threshold (Democratic only) --}}
                        @if($decision_type === 'democratic')
                            <div class="pt-4 border-t border-gray-100">
                                <div class="flex justify-between items-center mb-3">
                                    <label class="text-sm font-medium text-gray-700">
                                        {{ __('Pass Threshold (Yes Votes Needed)') }}
                                    </label>
                                    <span class="text-lg font-bold text-green-600">{{ $pass_threshold }}%</span>
                                </div>
                                <input type="range" 
                                       wire:model.live="pass_threshold" 
                                       min="50" max="100" step="5"
                                       class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-green-600">
                                <div class="flex justify-between text-xs text-gray-400 mt-1">
                                    <span>50% ({{ __('Simple Majority') }})</span>
                                    <span>67%</span>
                                    <span>100%</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    {{ __('Percentage of "Yes" votes needed for the proposal to pass.') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Voting Options --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                            <x-heroicon-o-adjustments-horizontal class="w-5 h-5 text-gray-500" />
                            {{ __('Voting Options') }}
                            <span class="text-gray-400 text-sm font-normal">投票オプション</span>
                        </h3>
                    </div>

                    <div class="p-6 space-y-4">
                        <label class="flex items-start gap-4 p-4 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                            <input type="checkbox" 
                                   wire:model.live="allow_anonymous_voting"
                                   class="w-5 h-5 mt-0.5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                            <div>
                                <span class="font-medium text-gray-900">{{ __('Anonymous Voting') }}</span>
                                <span class="text-gray-400 text-sm ml-1">匿名投票</span>
                                <p class="text-sm text-gray-500 mt-1">{{ __("Voters' identities will be hidden from other members.") }}</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-4 p-4 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                            <input type="checkbox" 
                                   wire:model.live="show_results_during_voting"
                                   class="w-5 h-5 mt-0.5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                            <div>
                                <span class="font-medium text-gray-900">{{ __('Show Live Results') }}</span>
                                <span class="text-gray-400 text-sm ml-1">リアルタイム結果</span>
                                <p class="text-sm text-gray-500 mt-1">{{ __('Display vote tallies while voting is in progress.') }}</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        @endif

        {{-- ════════════════════════════════════════════════════════════
             STEP 3: PARTICIPANTS
             ════════════════════════════════════════════════════════════ --}}
        @if($step === 3)
            <div class="space-y-6">
                {{-- Role Selection --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-indigo-50">
                        <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                            <x-heroicon-o-users class="w-6 h-6 text-purple-600" />
                            {{ __('Who Can Participate?') }}
                            <span class="text-gray-400 text-base font-normal">参加者</span>
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">{{ __('Select which member roles can view and vote on this proposal') }}</p>
                    </div>

                    <div class="p-6">
                        <div class="space-y-3">
                            @foreach($roleOptions as $role => $roleInfo)
                                <label class="flex items-center gap-4 p-4 rounded-xl border-2 cursor-pointer transition-all
                                              {{ in_array($role, $allowed_roles) 
                                                 ? 'border-green-500 bg-green-50' 
                                                 : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">
                                    <input type="checkbox" 
                                           wire:click="toggleRole('{{ $role }}')"
                                           @checked(in_array($role, $allowed_roles))
                                           class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                    <div class="flex-1">
                                        <span class="font-medium text-gray-900">{{ $roleInfo['name'] }}</span>
                                        <span class="text-gray-400 text-sm ml-1">({{ $roleInfo['name_ja'] }})</span>
                                        <p class="text-sm text-gray-500 mt-0.5">{{ $roleInfo['description'] }}</p>
                                    </div>
                                    @if(in_array($role, $allowed_roles))
                                        <x-heroicon-s-check-circle class="w-6 h-6 text-green-500 flex-shrink-0" />
                                    @endif
                                </label>
                            @endforeach
                        </div>
                        @error('allowed_roles')
                            <p class="text-sm text-red-600 mt-3 flex items-center gap-1">
                                <x-heroicon-s-exclamation-circle class="w-4 h-4" />
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- Invite Only Toggle --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-6">
                        <label class="flex items-start gap-4 cursor-pointer">
                            <input type="checkbox" 
                                   wire:click="toggleInviteOnly"
                                   @checked($is_invite_only)
                                   class="w-5 h-5 mt-0.5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                            <div>
                                <span class="font-semibold text-gray-900 text-lg">{{ __('Invite Only') }}</span>
                                <span class="text-gray-400 text-sm ml-1">招待のみ</span>
                                <p class="text-sm text-gray-500 mt-1">{{ __('Only specific invited members can participate in this proposal.') }}</p>
                            </div>
                        </label>

                        {{-- Invite User Search --}}
                        @if($is_invite_only)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Search and Invite Members') }}
                                    <span class="text-gray-400 font-normal ml-1">メンバーを検索</span>
                                </label>
                                <div class="relative">
                                    <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                    <input type="text" 
                                           wire:model.live.debounce.300ms="user_search"
                                           placeholder="{{ __('Search by name or email...') }}"
                                           class="w-full border border-gray-300 rounded-xl pl-10 pr-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>

                                {{-- Search Results --}}
                                @if($this->searchResults->count() > 0)
                                    <div class="mt-3 border border-gray-200 rounded-xl divide-y divide-gray-100 max-h-60 overflow-y-auto shadow-sm">
                                        @foreach($this->searchResults as $user)
                                            <button type="button"
                                                    wire:click="addInvitedUser({{ $user->id }})"
                                                    class="w-full text-left px-4 py-3 hover:bg-green-50 transition-colors flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-sm font-medium flex-shrink-0">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <span class="font-medium text-gray-900 block truncate">{{ $user->name }}</span>
                                                    <span class="text-sm text-gray-500 truncate block">{{ $user->email }}</span>
                                                </div>
                                                <x-heroicon-o-plus-circle class="w-5 h-5 text-green-500 flex-shrink-0" />
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif(strlen($user_search) >= 2)
                                    <p class="text-sm text-gray-500 mt-3">{{ __('No members found matching your search.') }}</p>
                                @endif

                                {{-- Invited Users List --}}
                                @if($this->invitedUsers->count() > 0)
                                    <div class="mt-4">
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ __('Invited Members') }} ({{ $this->invitedUsers->count() }})
                                        </span>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @foreach($this->invitedUsers as $user)
                                                <span class="inline-flex items-center gap-2 px-3 py-2 bg-green-100 text-green-800 rounded-full text-sm">
                                                    <span class="font-medium">{{ $user->name }}</span>
                                                    <button type="button" 
                                                            wire:click="removeInvitedUser({{ $user->id }})" 
                                                            class="hover:text-green-600 transition-colors">
                                                        <x-heroicon-s-x-mark class="w-4 h-4" />
                                                    </button>
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                @error('invited_user_ids')
                                    <p class="text-sm text-red-600 mt-3 flex items-center gap-1">
                                        <x-heroicon-s-exclamation-circle class="w-4 h-4" />
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ════════════════════════════════════════════════════════════
             STEP 4: REVIEW & SUBMIT
             ════════════════════════════════════════════════════════════ --}}
        @if($step === 4)
            <div class="space-y-6">
                {{-- Timeline --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50">
                        <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                            <x-heroicon-o-calendar class="w-6 h-6 text-amber-600" />
                            {{ __('Timeline') }}
                            <span class="text-gray-400 text-base font-normal">タイムライン</span>
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">{{ __('Set optional deadlines for feedback and voting periods') }}</p>
                    </div>

                    <div class="p-6">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Feedback Deadline') }}
                                    <span class="text-gray-400 font-normal ml-1">フィードバック期限</span>
                                </label>
                                <input type="datetime-local" 
                                       wire:model="feedback_deadline"
                                       class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent
                                              @error('feedback_deadline') border-red-300 @enderror">
                                <p class="text-xs text-gray-500 mt-1">{{ __('When the feedback period ends') }}</p>
                                @error('feedback_deadline')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Voting Deadline') }}
                                    <span class="text-gray-400 font-normal ml-1">投票期限</span>
                                </label>
                                <input type="datetime-local" 
                                       wire:model="voting_deadline"
                                       class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent
                                              @error('voting_deadline') border-red-300 @enderror">
                                <p class="text-xs text-gray-500 mt-1">{{ __('When voting automatically closes') }}</p>
                                @error('voting_deadline')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Supporting Documents --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                            <x-heroicon-o-paper-clip class="w-5 h-5 text-gray-500" />
                            {{ __('Supporting Documents') }}
                            <span class="text-gray-400 text-sm font-normal">添付資料</span>
                            <span class="text-xs text-gray-400 font-normal ml-1">({{ __('optional') }})</span>
                        </h3>
                    </div>

                    <div class="p-6">
                        <label class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-green-400 hover:bg-green-50 transition-colors">
                            <div wire:loading.remove wire:target="documents" class="text-center">
                                <x-heroicon-o-arrow-up-tray class="w-10 h-10 text-gray-400 mx-auto mb-2" />
                                <span class="text-sm text-gray-600 font-medium">{{ __('Click to upload files') }}</span>
                                <span class="text-xs text-gray-400 block mt-1">PDF, DOC, XLS, PPT, {{ __('images') }} ({{ __('max') }} 10MB)</span>
                            </div>
                            <div wire:loading wire:target="documents" class="text-center">
                                <svg class="animate-spin h-10 w-10 text-green-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span class="text-sm text-gray-600">{{ __('Uploading...') }}</span>
                            </div>
                            <input type="file" 
                                   wire:model="documents" 
                                   multiple 
                                   class="hidden"
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.webp">
                        </label>

                        @error('documents.*')
                            <p class="text-sm text-red-600 mt-3 flex items-center gap-1">
                                <x-heroicon-s-exclamation-circle class="w-4 h-4" />
                                {{ $message }}
                            </p>
                        @enderror

                        {{-- Uploaded Documents List --}}
                        @if(count($documents) > 0)
                            <div class="mt-4 space-y-2">
                                @foreach($documents as $index => $doc)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-200">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-lg bg-white border border-gray-200 flex items-center justify-center flex-shrink-0">
                                                <x-heroicon-o-document class="w-5 h-5 text-gray-400" />
                                            </div>
                                            <div class="min-w-0">
                                                <span class="text-sm text-gray-700 font-medium truncate block">{{ $doc->getClientOriginalName() }}</span>
                                                <span class="text-xs text-gray-400">{{ number_format($doc->getSize() / 1024, 0) }} KB</span>
                                            </div>
                                        </div>
                                        <button type="button" 
                                                wire:click="removeDocument({{ $index }})"
                                                class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0">
                                            <x-heroicon-o-trash class="w-5 h-5" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Summary Card --}}
                <div class="bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 rounded-2xl border border-green-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-green-200/50">
                        <h3 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                            📋 {{ __('Proposal Summary') }}
                            <span class="text-gray-400 text-base font-normal">提案概要</span>
                        </h3>
                    </div>

                    <div class="p-6">
                        <dl class="space-y-4">
                            <div class="flex justify-between items-start pb-3 border-b border-green-200/30">
                                <dt class="text-gray-500 text-sm">{{ __('Title') }}</dt>
                                <dd class="text-gray-900 font-medium text-right max-w-[65%]">{{ $title ?: '—' }}</dd>
                            </div>
                            <div class="flex justify-between items-center pb-3 border-b border-green-200/30">
                                <dt class="text-gray-500 text-sm">{{ __('Decision Type') }}</dt>
                                <dd class="text-gray-900">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white rounded-lg text-sm font-medium">
                                        <x-dynamic-component :component="'heroicon-s-' . ($decisionTypeOptions[$decision_type]['icon'] ?? 'question-mark-circle')" class="w-4 h-4 text-gray-500" />
                                        {{ $decisionTypeOptions[$decision_type]['name'] ?? '—' }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between items-center pb-3 border-b border-green-200/30">
                                <dt class="text-gray-500 text-sm">{{ __('Quorum') }}</dt>
                                <dd class="text-gray-900 font-semibold">{{ $quorum_percentage }}%</dd>
                            </div>
                            @if($decision_type === 'democratic')
                                <div class="flex justify-between items-center pb-3 border-b border-green-200/30">
                                    <dt class="text-gray-500 text-sm">{{ __('Pass Threshold') }}</dt>
                                    <dd class="text-gray-900 font-semibold">{{ $pass_threshold }}%</dd>
                                </div>
                            @endif
                            <div class="flex justify-between items-center pb-3 border-b border-green-200/30">
                                <dt class="text-gray-500 text-sm">{{ __('Participants') }}</dt>
                                <dd class="text-gray-900">
                                    @if($is_invite_only)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-purple-100 text-purple-700 rounded-lg text-sm font-medium">
                                            <x-heroicon-s-user-plus class="w-4 h-4" />
                                            {{ count($invited_user_ids) }} {{ __('invited') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-100 text-blue-700 rounded-lg text-sm font-medium">
                                            <x-heroicon-s-users class="w-4 h-4" />
                                            {{ count($allowed_roles) }} {{ __('role(s)') }}
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between items-center pb-3 border-b border-green-200/30">
                                <dt class="text-gray-500 text-sm">{{ __('Voting Options') }}</dt>
                                <dd class="text-gray-900 text-sm">
                                    @if($allow_anonymous_voting)
                                        <span class="inline-flex items-center gap-1 text-gray-600">
                                            <x-heroicon-s-eye-slash class="w-4 h-4" />
                                            {{ __('Anonymous') }}
                                        </span>
                                    @else
                                        <span class="text-gray-500">{{ __('Public') }}</span>
                                    @endif
                                    •
                                    @if($show_results_during_voting)
                                        <span class="text-gray-600">{{ __('Live results') }}</span>
                                    @else
                                        <span class="text-gray-500">{{ __('Hidden until closed') }}</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between items-center">
                                <dt class="text-gray-500 text-sm">{{ __('Documents') }}</dt>
                                <dd class="text-gray-900 font-medium">{{ count($documents) }} {{ __('file(s)') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        @endif

        {{-- Error Display --}}
        @error('submit')
            <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
                <x-heroicon-s-exclamation-triangle class="w-6 h-6 text-red-500 flex-shrink-0" />
                <div>
                    <p class="font-medium text-red-800">{{ __('Error') }}</p>
                    <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                </div>
            </div>
        @enderror
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         BOTTOM NAVIGATION (Fixed)
         ════════════════════════════════════════════════════════════════ --}}
    <div class="fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 p-4 z-20 shadow-lg">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            {{-- Back Button --}}
            @if($step > 1)
                <button type="button" 
                        wire:click="previousStep"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50"
                        class="px-6 py-3 text-gray-600 font-medium hover:bg-gray-100 rounded-xl transition-colors flex items-center gap-2">
                    <x-heroicon-o-arrow-left class="w-5 h-5" />
                    {{ __('Back') }}
                </button>
            @else
                <div></div>
            @endif

            {{-- Next / Submit Button --}}
            @if($step < $totalSteps)
                <button type="button" 
                        wire:click="nextStep"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50"
                        wire:target="nextStep"
                        class="px-8 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors shadow-lg hover:shadow-xl flex items-center gap-2">
                    <span wire:loading.remove wire:target="nextStep">
                        {{ __('Next') }}
                        <x-heroicon-o-arrow-right class="w-5 h-5 inline ml-1" />
                    </span>
                    <span wire:loading wire:target="nextStep" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        {{ __('Validating...') }}
                    </span>
                </button>
            @else
                <button type="button" 
                        wire:click="submit"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50"
                        wire:target="submit"
                        class="px-8 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors shadow-lg hover:shadow-xl flex items-center gap-2">
                    <span wire:loading.remove wire:target="submit">
                        <x-heroicon-s-check class="w-5 h-5 inline mr-1" />
                        {{ __('Create Proposal') }}
                    </span>
                    <span wire:loading wire:target="submit" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        {{ __('Creating...') }}
                    </span>
                </button>
            @endif
        </div>
    </div>
</div>