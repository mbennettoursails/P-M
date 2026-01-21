<div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('decisions.voting.title') }}</h2>

    @if(!$proposal->is_voting_active && !$hasVoted)
        {{-- Voting Not Active --}}
        <div class="text-center py-8 text-gray-500">
            <x-heroicon-o-clock class="w-12 h-12 mx-auto mb-3 text-gray-300" />
            <p>{{ $proposal->current_stage === 'closed' 
                  ? __('decisions.voting.voting_closed') 
                  : __('decisions.voting.not_started') }}</p>
        </div>
    @elseif($hasVoted && !$isChangingVote)
        {{-- Already Voted --}}
        <div class="text-center py-6">
            <div class="w-16 h-16 mx-auto rounded-full bg-{{ $userVote->vote_color }}-100 flex items-center justify-center mb-4">
                <x-dynamic-component :component="'heroicon-o-' . $userVote->vote_icon" 
                                     class="w-8 h-8 text-{{ $userVote->vote_color }}-600" />
            </div>
            <p class="text-lg font-medium text-gray-900 mb-1">{{ __('decisions.voting.your_vote') }}</p>
            <p class="text-2xl font-bold text-{{ $userVote->vote_color }}-600">
                {{ $userVote->vote_value_label }}
            </p>
            @if($userVote->reason)
                <p class="text-sm text-gray-500 mt-3 p-3 bg-gray-50 rounded-lg">
                    "{{ $userVote->reason }}"
                </p>
            @endif
            @if($userVote->is_anonymous)
                <p class="text-xs text-gray-400 mt-2">
                    <x-heroicon-o-eye-slash class="w-4 h-4 inline" />
                    {{ __('decisions.labels.anonymous') }}
                </p>
            @endif
            @if($userVote->was_changed)
                <p class="text-xs text-gray-400 mt-1">
                    変更: {{ $userVote->changed_at->format('m/d H:i') }}
                </p>
            @endif

            @if($canVote)
                <button wire:click="toggleChangeVote"
                        class="mt-4 text-sm text-coop-600 hover:text-coop-700">
                    <x-heroicon-o-pencil class="w-4 h-4 inline mr-1" />
                    {{ __('decisions.voting.change_vote') }}
                </button>
            @endif
        </div>
    @else
        {{-- Voting Form --}}
        <div class="space-y-4">
            {{-- Vote Options --}}
            <div class="grid grid-cols-1 sm:grid-cols-{{ count($voteOptions) <= 3 ? count($voteOptions) : '2' }} gap-3">
                @foreach($voteOptions as $option)
                    <button wire:click="selectVote('{{ $option['value'] }}')"
                            class="flex flex-col items-center p-4 rounded-xl border-2 transition-all
                                   {{ $selectedVote === $option['value'] 
                                      ? 'border-' . $option['color'] . '-500 bg-' . $option['color'] . '-50' 
                                      : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center mb-2
                                    {{ $selectedVote === $option['value'] 
                                       ? 'bg-' . $option['color'] . '-500 text-white' 
                                       : 'bg-gray-100 text-gray-500' }}">
                            <x-dynamic-component :component="'heroicon-o-' . $option['icon']" class="w-6 h-6" />
                        </div>
                        <span class="font-medium {{ $selectedVote === $option['value'] ? 'text-' . $option['color'] . '-700' : 'text-gray-700' }}">
                            {{ app()->getLocale() === 'ja' ? $option['label'] : $option['label_en'] }}
                        </span>
                    </button>
                @endforeach
            </div>

            {{-- Reason Input --}}
            @if($showReasonInput)
                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('decisions.labels.reason') }}
                        </label>
                        <textarea wire:model="reason"
                                  rows="3"
                                  placeholder="{{ __('decisions.voting.reason_placeholder') }}"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500 focus:border-transparent"></textarea>
                    </div>

                    {{-- Anonymous Option --}}
                    @if($showAnonymousOption)
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   wire:model="isAnonymous"
                                   class="rounded text-coop-600 focus:ring-coop-500">
                            <span class="ml-2 text-sm text-gray-700">
                                <x-heroicon-o-eye-slash class="w-4 h-4 inline mr-1" />
                                {{ __('decisions.voting.vote_anonymous') }}
                            </span>
                        </label>
                    @endif

                    {{-- Submit Button --}}
                    <div class="flex gap-3">
                        @if($isChangingVote)
                            <button wire:click="cancelChange"
                                    class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                {{ __('decisions.actions.cancel') }}
                            </button>
                        @endif
                        <button wire:click="submitVote"
                                class="flex-1 px-4 py-3 bg-coop-600 text-white rounded-lg hover:bg-coop-700 font-medium
                                       disabled:opacity-50 disabled:cursor-not-allowed"
                                {{ !$selectedVote ? 'disabled' : '' }}>
                            {{ $isChangingVote ? __('decisions.actions.change_vote') : __('decisions.actions.submit_vote') }}
                        </button>
                    </div>

                    @error('vote')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror
                </div>
            @endif
        </div>
    @endif
</div>
