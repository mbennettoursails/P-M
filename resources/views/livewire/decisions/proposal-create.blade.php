<div class="min-h-screen bg-gray-50 pb-32">
    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-3xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('decisions.index') }}" class="text-gray-500 hover:text-gray-700">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </a>
                <h1 class="text-lg font-semibold text-gray-900">New Proposal</h1>
                <button wire:click="saveDraft" class="text-sm text-gray-500 hover:text-gray-700">
                    Save Draft
                </button>
            </div>
        </div>
    </header>

    {{-- Step Indicator --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-3xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                @foreach($steps as $stepNum => $stepInfo)
                    <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium
                                        {{ $step === $stepNum ? 'bg-green-600 text-white' : 
                                           ($step > $stepNum ? 'bg-green-100 text-green-600' : 'bg-gray-200 text-gray-500') }}">
                                @if($step > $stepNum)
                                    <x-heroicon-s-check class="w-5 h-5" />
                                @else
                                    {{ $stepNum }}
                                @endif
                            </div>
                            <span class="text-xs mt-1 text-gray-500 hidden sm:block">{{ $stepInfo['title'] }}</span>
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
    <div class="max-w-3xl mx-auto px-4 py-6">
        {{-- Step 1: Basic Information --}}
        @if($step === 1)
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Basic Information</h2>
                <p class="text-sm text-gray-500 mb-6">Enter the title and description of your proposal</p>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               wire:model="title"
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="Enter a clear, descriptive title">
                        @error('title')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 mt-1">{{ strlen($title) }}/255 characters</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea wire:model="description"
                                  rows="8"
                                  class="w-full border border-gray-300 rounded-lg px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                  placeholder="Provide detailed information about your proposal..."></textarea>
                        @error('description')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 mt-1">{{ strlen($description) }}/10,000 characters</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 2: Decision Settings --}}
        @if($step === 2)
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Decision Settings</h2>
                <p class="text-sm text-gray-500 mb-6">Configure the voting method and conditions</p>
                
                <div class="space-y-6">
                    {{-- Decision Type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Decision Type</label>
                        <div class="space-y-3">
                            @foreach($decisionTypes as $key => $type)
                                <button type="button"
                                        wire:click="$set('decision_type', '{{ $key }}')"
                                        class="w-full flex items-start p-4 rounded-xl border-2 transition-all text-left
                                               {{ $decision_type === $key 
                                                  ? 'border-green-500 bg-green-50' 
                                                  : 'border-gray-200 hover:border-gray-300' }}">
                                    <div class="w-10 h-10 rounded-lg bg-{{ $type['color'] }}-100 text-{{ $type['color'] }}-600 
                                                flex items-center justify-center flex-shrink-0">
                                        <x-dynamic-component :component="'heroicon-o-' . $type['icon']" class="w-5 h-5" />
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="font-medium text-gray-900">{{ $type['name'] }}</p>
                                        <p class="text-sm text-gray-500 mt-0.5">{{ $type['description'] }}</p>
                                    </div>
                                    @if($decision_type === $key)
                                        <x-heroicon-s-check-circle class="w-6 h-6 text-green-500 flex-shrink-0" />
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                    
                    {{-- Quorum --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Quorum Percentage
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Minimum participation required for a valid vote</p>
                        <div class="flex items-center space-x-4">
                            <input type="range" 
                                   wire:model.live="quorum_percentage"
                                   min="25" max="100" step="5"
                                   class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-green-600">
                            <span class="w-12 text-center font-medium text-gray-900">{{ $quorum_percentage }}%</span>
                        </div>
                    </div>
                    
                    {{-- Pass Threshold (only for democratic) --}}
                    @if($decision_type === 'democratic')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Pass Threshold
                            </label>
                            <p class="text-xs text-gray-500 mb-2">Percentage of "Yes" votes needed to pass</p>
                            <div class="flex items-center space-x-4">
                                <input type="range" 
                                       wire:model.live="pass_threshold"
                                       min="50" max="100" step="5"
                                       class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-green-600">
                                <span class="w-12 text-center font-medium text-gray-900">{{ $pass_threshold }}%</span>
                            </div>
                        </div>
                    @endif
                    
                    {{-- Toggles --}}
                    <div class="space-y-4 pt-4 border-t border-gray-100">
                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <p class="font-medium text-gray-900">Anonymous Voting</p>
                                <p class="text-sm text-gray-500">Hide voter identities from other participants</p>
                            </div>
                            <button type="button"
                                    wire:click="$toggle('allow_anonymous_voting')"
                                    class="relative w-11 h-6 rounded-full transition-colors
                                           {{ $allow_anonymous_voting ? 'bg-green-600' : 'bg-gray-200' }}">
                                <span class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform
                                             {{ $allow_anonymous_voting ? 'translate-x-5' : '' }}"></span>
                            </button>
                        </label>
                        
                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <p class="font-medium text-gray-900">Show Results During Voting</p>
                                <p class="text-sm text-gray-500">Display vote counts before voting ends</p>
                            </div>
                            <button type="button"
                                    wire:click="$toggle('show_results_during_voting')"
                                    class="relative w-11 h-6 rounded-full transition-colors
                                           {{ $show_results_during_voting ? 'bg-green-600' : 'bg-gray-200' }}">
                                <span class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform
                                             {{ $show_results_during_voting ? 'translate-x-5' : '' }}"></span>
                            </button>
                        </label>
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 3: Participants --}}
        @if($step === 3)
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Participants</h2>
                <p class="text-sm text-gray-500 mb-6">Set who can view and participate in this proposal</p>
                
                <div class="space-y-6">
                    {{-- Role Selection --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Allowed Roles</label>
                        <div class="space-y-2">
                            @foreach(['reijikai' => 'Committee Members', 'shokuin' => 'Staff', 'volunteer' => 'Volunteers'] as $role => $label)
                                <label class="flex items-center p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox"
                                           wire:model="allowed_roles"
                                           value="{{ $role }}"
                                           class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                    <span class="ml-3 text-gray-900">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('allowed_roles')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    {{-- Invite Only Toggle --}}
                    <div class="pt-4 border-t border-gray-100">
                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <p class="font-medium text-gray-900">Invite Only</p>
                                <p class="text-sm text-gray-500">Only specifically invited users can participate</p>
                            </div>
                            <button type="button"
                                    wire:click="$toggle('is_invite_only')"
                                    class="relative w-11 h-6 rounded-full transition-colors
                                           {{ $is_invite_only ? 'bg-green-600' : 'bg-gray-200' }}">
                                <span class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform
                                             {{ $is_invite_only ? 'translate-x-5' : '' }}"></span>
                            </button>
                        </label>
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 4: Timeline & Documents --}}
        @if($step === 4)
            <div class="space-y-6">
                {{-- Timeline --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-1">Timeline</h2>
                    <p class="text-sm text-gray-500 mb-6">Set deadlines for feedback and voting (optional)</p>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Feedback Deadline</label>
                            <input type="datetime-local"
                                   wire:model="feedback_deadline"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            @error('feedback_deadline')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Voting Deadline</label>
                            <input type="datetime-local"
                                   wire:model="voting_deadline"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            @error('voting_deadline')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                {{-- Documents --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-1">Documents</h2>
                    <p class="text-sm text-gray-500 mb-6">Attach supporting files (optional)</p>
                    
                    <div>
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-gray-400 hover:bg-gray-50 transition-colors">
                            <x-heroicon-o-arrow-up-tray class="w-8 h-8 text-gray-400 mb-2" />
                            <span class="text-sm text-gray-500">Click to upload files</span>
                            <span class="text-xs text-gray-400 mt-1">PDF, DOC, XLS, PPT, images up to 10MB</span>
                            <input type="file"
                                   wire:model="documents"
                                   multiple
                                   class="hidden"
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.webp">
                        </label>
                        
                        @if(count($documents) > 0)
                            <div class="mt-4 space-y-2">
                                @foreach($documents as $index => $doc)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center min-w-0">
                                            <x-heroicon-o-document class="w-5 h-5 text-gray-400 flex-shrink-0" />
                                            <span class="ml-2 text-sm text-gray-700 truncate">{{ $doc->getClientOriginalName() }}</span>
                                        </div>
                                        <button type="button"
                                                wire:click="removeDocument({{ $index }})"
                                                class="p-1 text-gray-400 hover:text-red-500">
                                            <x-heroicon-o-x-mark class="w-5 h-5" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        
                        @error('documents.*')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        @endif

        {{-- Error Display --}}
        @error('submit')
            <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-600">{{ $message }}</p>
            </div>
        @enderror
    </div>

    {{-- Bottom Navigation --}}
    <div class="fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 p-4 z-20">
        <div class="max-w-3xl mx-auto flex justify-between">
            @if($step > 1)
                <button wire:click="previousStep"
                        class="px-6 py-3 text-gray-600 font-medium hover:bg-gray-100 rounded-xl transition-colors">
                    <x-heroicon-o-arrow-left class="w-5 h-5 inline mr-1" />
                    Back
                </button>
            @else
                <div></div>
            @endif
            
            @if($step < $totalSteps)
                <button wire:click="nextStep"
                        class="px-6 py-3 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 transition-colors">
                    Next
                    <x-heroicon-o-arrow-right class="w-5 h-5 inline ml-1" />
                </button>
            @else
                <button wire:click="submit"
                        class="px-6 py-3 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 transition-colors">
                    <x-heroicon-o-check class="w-5 h-5 inline mr-1" />
                    Create Proposal
                </button>
            @endif
        </div>
    </div>
</div>
