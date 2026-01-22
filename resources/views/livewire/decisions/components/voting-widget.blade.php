<div class="space-y-6">
    {{-- Voting Section --}}
    @if($canVote || $hasVoted)
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                @if($hasVoted && !$isChangingVote)
                    Your Vote
                @else
                    Cast Your Vote
                @endif
            </h3>
            
            {{-- Already Voted State --}}
            @if($hasVoted && !$isChangingVote)
                <div class="mb-4">
                    @php
                        $voteConfig = $userVote->vote_config;
                    @endphp
                    <div class="flex items-center p-4 rounded-xl bg-{{ $voteConfig['color'] }}-50 border-2 border-{{ $voteConfig['color'] }}-500">
                        <div class="w-10 h-10 rounded-full bg-{{ $voteConfig['color'] }}-500 text-white flex items-center justify-center">
                            <x-dynamic-component :component="'heroicon-s-' . $voteConfig['icon']" class="w-5 h-5" />
                        </div>
                        <div class="ml-4">
                            <p class="font-medium text-gray-900">{{ $voteConfig['label'] }}</p>
                            @if($userVote->reason)
                                <p class="text-sm text-gray-600 mt-1">{{ $userVote->reason }}</p>
                            @endif
                        </div>
                        <x-heroicon-s-check-circle class="w-6 h-6 ml-auto text-{{ $voteConfig['color'] }}-500" />
                    </div>
                    
                    @if($canVote)
                        <button wire:click="changeVote"
                                class="mt-3 text-sm text-gray-500 hover:text-gray-700 underline">
                            Change vote
                        </button>
                    @endif
                </div>
            @endif

            {{-- Vote Options --}}
            @if($canVote && (!$hasVoted || $isChangingVote))
                <div class="space-y-3">
                    @foreach($voteOptions as $option)
                        <button wire:click="selectVote('{{ $option['value'] }}')"
                                class="flex items-center w-full p-4 rounded-xl border-2 transition-all
                                       {{ $selectedVote === $option['value'] 
                                          ? 'border-' . $option['color'] . '-500 bg-' . $option['color'] . '-50' 
                                          : 'border-gray-200 hover:border-' . $option['color'] . '-300' }}">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center
                                        {{ $selectedVote === $option['value'] 
                                           ? 'bg-' . $option['color'] . '-500 text-white' 
                                           : 'bg-' . $option['color'] . '-100 text-' . $option['color'] . '-600' }}">
                                <x-dynamic-component :component="'heroicon-s-' . $option['icon']" class="w-5 h-5" />
                            </div>
                            <span class="ml-4 font-medium text-gray-900">{{ $option['label'] }}</span>
                            @if($selectedVote === $option['value'])
                                <x-heroicon-s-check class="w-5 h-5 ml-auto text-{{ $option['color'] }}-500" />
                            @endif
                        </button>
                    @endforeach
                </div>

                {{-- Reason Input --}}
                @if($showReasonInput)
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Reason (optional)
                            </label>
                            <textarea wire:model="reason"
                                      rows="3"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                      placeholder="Explain your vote..."></textarea>
                        </div>
                        
                        @if($showAnonymousOption)
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox"
                                       wire:model="isAnonymous"
                                       class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                <span class="ml-2 text-sm text-gray-700">Vote anonymously</span>
                            </label>
                        @endif
                        
                        @error('vote')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        
                        <div class="flex space-x-3">
                            @if($isChangingVote)
                                <button wire:click="cancelChange"
                                        class="flex-1 px-4 py-3 text-gray-600 font-medium rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors">
                                    Cancel
                                </button>
                            @endif
                            <button wire:click="submitVote"
                                    class="flex-1 px-4 py-3 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 transition-colors">
                                {{ $isChangingVote ? 'Update Vote' : 'Submit Vote' }}
                            </button>
                        </div>
                    </div>
                @endif
            @endif

            {{-- Cannot Vote Message --}}
            @if(!$canVote && !$hasVoted)
                <p class="text-sm text-gray-500 italic">
                    @if($proposal->current_stage !== 'voting')
                        Voting is not currently open for this proposal.
                    @elseif($proposal->is_overdue)
                        The voting deadline has passed.
                    @else
                        You do not have permission to vote on this proposal.
                    @endif
                </p>
            @endif
        </div>
    @endif

    {{-- Results Section --}}
    @if($showResults)
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Results</h3>
            
            <div class="space-y-3">
                @foreach($voteResults as $option => $data)
                    <div class="flex items-center">
                        <div class="w-24 flex items-center">
                            <div class="w-6 h-6 rounded-full bg-{{ $data['color'] }}-100 text-{{ $data['color'] }}-600 
                                        flex items-center justify-center mr-2">
                                <x-dynamic-component :component="'heroicon-s-' . $data['icon']" class="w-3.5 h-3.5" />
                            </div>
                            <span class="text-sm font-medium text-gray-700">{{ $data['label'] }}</span>
                        </div>
                        <div class="flex-1 mx-3">
                            <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-{{ $data['color'] }}-500 rounded-full transition-all duration-500"
                                     style="width: {{ $data['percentage'] }}%"></div>
                            </div>
                        </div>
                        <div class="w-20 text-sm text-gray-600 text-right">
                            {{ $data['percentage'] }}% ({{ $data['count'] }})
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Quorum Status --}}
            <div class="mt-6 pt-4 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-sm text-gray-600">
                        <x-heroicon-o-users class="w-4 h-4 mr-1.5" />
                        <span>Quorum: {{ $quorumStatus['current'] }}/{{ $quorumStatus['eligible'] }} ({{ $quorumStatus['percentage'] }}%)</span>
                    </div>
                    @if($quorumStatus['met'])
                        <span class="inline-flex items-center text-sm font-medium text-green-600">
                            <x-heroicon-s-check-circle class="w-4 h-4 mr-1" />
                            Met
                        </span>
                    @else
                        <span class="inline-flex items-center text-sm font-medium text-amber-600">
                            <x-heroicon-s-exclamation-triangle class="w-4 h-4 mr-1" />
                            {{ $quorumStatus['needed'] }} more needed
                        </span>
                    @endif
                </div>
                
                {{-- Quorum Progress Bar --}}
                <div class="mt-2 h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full {{ $quorumStatus['met'] ? 'bg-green-500' : 'bg-amber-500' }} rounded-full transition-all duration-500"
                         style="width: {{ min($quorumStatus['percentage'], 100) }}%"></div>
                </div>
            </div>
        </div>
    @elseif(!$showResults && $proposal->is_voting)
        <div class="bg-gray-50 rounded-xl border border-gray-200 p-6 text-center">
            <x-heroicon-o-eye-slash class="w-8 h-8 text-gray-400 mx-auto mb-2" />
            <p class="text-sm text-gray-500">Results will be visible after you vote</p>
        </div>
    @endif
</div>
