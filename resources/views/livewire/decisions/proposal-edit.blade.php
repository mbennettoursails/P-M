<div class="min-h-screen bg-gray-50 pb-32">
    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-3xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('decisions.show', $proposal) }}" class="text-gray-500 hover:text-gray-700">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </a>
                <h1 class="text-lg font-semibold text-gray-900">Edit Proposal</h1>
                <div></div>
            </div>
        </div>
    </header>

    {{-- Form Content --}}
    <div class="max-w-3xl mx-auto px-4 py-6 space-y-6">
        {{-- Basic Information --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
            
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
                </div>
            </div>
        </div>

        {{-- Decision Settings --}}
        @if($canEditSettings)
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Decision Settings</h2>
                
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quorum Percentage</label>
                        <div class="flex items-center space-x-4">
                            <input type="range" 
                                   wire:model.live="quorum_percentage"
                                   min="25" max="100" step="5"
                                   class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-green-600">
                            <span class="w-12 text-center font-medium text-gray-900">{{ $quorum_percentage }}%</span>
                        </div>
                    </div>
                    
                    {{-- Pass Threshold --}}
                    @if($decision_type === 'democratic')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pass Threshold</label>
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
                                <p class="text-sm text-gray-500">Hide voter identities</p>
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
        @else
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                <div class="flex">
                    <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-amber-500 flex-shrink-0" />
                    <p class="ml-2 text-sm text-amber-700">
                        Decision settings can only be changed while in draft stage.
                    </p>
                </div>
            </div>
        @endif

        {{-- Participants --}}
        @if($canEditSettings)
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Participants</h2>
                
                <div class="space-y-4">
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
                    
                    <label class="flex items-center justify-between cursor-pointer pt-4 border-t border-gray-100">
                        <div>
                            <p class="font-medium text-gray-900">Invite Only</p>
                            <p class="text-sm text-gray-500">Only invited users can participate</p>
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
        @endif

        {{-- Timeline --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h2>
            
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
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Documents</h2>
            
            {{-- Existing Documents --}}
            @if($proposal->documents->count() > 0)
                <div class="space-y-2 mb-4">
                    @foreach($proposal->documents as $document)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center min-w-0">
                                <x-dynamic-component :component="'heroicon-o-' . $document->icon" 
                                                     class="w-5 h-5 text-{{ $document->color }}-500 flex-shrink-0" />
                                <span class="ml-2 text-sm text-gray-700 truncate">{{ $document->title }}</span>
                                <span class="ml-2 text-xs text-gray-400">{{ $document->file_size_formatted }}</span>
                            </div>
                            <button wire:click="deleteDocument({{ $document->id }})"
                                    wire:confirm="Delete this document?"
                                    class="p-1 text-gray-400 hover:text-red-500">
                                <x-heroicon-o-trash class="w-5 h-5" />
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
            
            {{-- Upload New Documents --}}
            <div>
                <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-gray-400 hover:bg-gray-50 transition-colors">
                    <x-heroicon-o-arrow-up-tray class="w-6 h-6 text-gray-400 mb-1" />
                    <span class="text-sm text-gray-500">Add more files</span>
                    <input type="file"
                           wire:model="newDocuments"
                           multiple
                           class="hidden"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.webp">
                </label>
                
                @if(count($newDocuments) > 0)
                    <div class="mt-3 space-y-2">
                        @foreach($newDocuments as $index => $doc)
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                <div class="flex items-center min-w-0">
                                    <x-heroicon-o-document class="w-5 h-5 text-green-500 flex-shrink-0" />
                                    <span class="ml-2 text-sm text-gray-700 truncate">{{ $doc->getClientOriginalName() }}</span>
                                    <span class="ml-1 text-xs text-green-600">(new)</span>
                                </div>
                                <button wire:click="removeNewDocument({{ $index }})"
                                        class="p-1 text-gray-400 hover:text-red-500">
                                    <x-heroicon-o-x-mark class="w-5 h-5" />
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
                
                @error('newDocuments.*')
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Error Display --}}
        @error('save')
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-600">{{ $message }}</p>
            </div>
        @enderror
    </div>

    {{-- Bottom Save Button --}}
    <div class="fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 p-4 z-20">
        <div class="max-w-3xl mx-auto flex justify-between">
            <a href="{{ route('decisions.show', $proposal) }}"
               class="px-6 py-3 text-gray-600 font-medium hover:bg-gray-100 rounded-xl transition-colors">
                Cancel
            </a>
            <button wire:click="save"
                    class="px-6 py-3 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 transition-colors">
                <x-heroicon-o-check class="w-5 h-5 inline mr-1" />
                Save Changes
            </button>
        </div>
    </div>
</div>
