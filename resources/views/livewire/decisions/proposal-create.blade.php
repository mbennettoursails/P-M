<div class="min-h-screen bg-gray-50 pb-20">
    {{-- Header --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ __('decisions.create.title') }}</h1>
                    <p class="text-sm text-gray-500">
                        {{ $stepTitles[$currentStep]['title'] }}
                    </p>
                </div>
                <a href="{{ route('decisions.index') }}" class="text-gray-400 hover:text-gray-600">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </a>
            </div>
        </div>
    </div>

    {{-- Progress Steps --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                @for($i = 1; $i <= $totalSteps; $i++)
                    <button wire:click="goToStep({{ $i }})"
                            @if($i > $currentStep) disabled @endif
                            class="flex items-center {{ $i > $currentStep ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium
                                     {{ $i < $currentStep ? 'bg-coop-600 text-white' : ($i === $currentStep ? 'bg-coop-100 text-coop-700 ring-2 ring-coop-600' : 'bg-gray-200 text-gray-500') }}">
                            @if($i < $currentStep)
                                <x-heroicon-o-check class="w-5 h-5" />
                            @else
                                {{ $i }}
                            @endif
                        </span>
                        <span class="hidden sm:block ml-2 text-sm {{ $i === $currentStep ? 'text-coop-700 font-medium' : 'text-gray-500' }}">
                            {{ $stepTitles[$i]['title'] }}
                        </span>
                    </button>
                    @if($i < $totalSteps)
                        <div class="flex-1 h-0.5 mx-2 {{ $i < $currentStep ? 'bg-coop-600' : 'bg-gray-200' }}"></div>
                    @endif
                @endfor
            </div>
        </div>
    </div>

    {{-- Form Content --}}
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            {{-- Step 1: Basic Info --}}
            @if($currentStep === 1)
                <div class="space-y-6">
                    <div>
                        <p class="text-sm text-gray-500 mb-6">{{ $stepTitles[1]['subtitle'] }}</p>
                    </div>

                    {{-- Title --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('decisions.create.title_label') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               wire:model="title"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500 focus:border-transparent"
                               placeholder="提案のタイトルを入力">
                        @error('title') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Title English --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('decisions.create.title_en_label') }}
                        </label>
                        <input type="text" 
                               wire:model="title_en"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500 focus:border-transparent"
                               placeholder="Title in English (optional)">
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('decisions.create.description_label') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea wire:model="description"
                                  rows="6"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500 focus:border-transparent"
                                  placeholder="提案の詳細を入力（20文字以上）"></textarea>
                        @error('description') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Description English --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('decisions.create.description_en_label') }}
                        </label>
                        <textarea wire:model="description_en"
                                  rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500 focus:border-transparent"
                                  placeholder="Description in English (optional)"></textarea>
                    </div>
                </div>
            @endif

            {{-- Step 2: Decision Configuration --}}
            @if($currentStep === 2)
                <div class="space-y-6">
                    <div>
                        <p class="text-sm text-gray-500 mb-6">{{ $stepTitles[2]['subtitle'] }}</p>
                    </div>

                    {{-- Decision Type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            {{ __('decisions.create.decision_type_label') }}
                        </label>
                        <div class="space-y-3">
                            @foreach($decisionTypes as $key => $type)
                                <label class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors
                                              {{ $decision_type === $key ? 'border-coop-500 bg-coop-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <input type="radio" 
                                           wire:model.live="decision_type" 
                                           value="{{ $key }}"
                                           class="mt-1 text-coop-600 focus:ring-coop-500">
                                    <div class="ml-3">
                                        <span class="block font-medium text-gray-900">{{ $type['name_ja'] }}</span>
                                        <span class="block text-sm text-gray-500 mt-1">{{ $type['description_ja'] }}</span>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @foreach($type['votes'] as $index => $vote)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs 
                                                             bg-{{ $type['vote_colors'][$vote] }}-100 text-{{ $type['vote_colors'][$vote] }}-700">
                                                    {{ $type['votes_ja'][$index] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Quorum & Threshold --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('decisions.create.quorum_label') }}
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       wire:model="quorum_percentage"
                                       min="1" max="100"
                                       class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400">%</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">参加者の何%が投票すれば有効か</p>
                        </div>

                        @if($decision_type === 'democratic')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('decisions.create.pass_threshold_label') }}
                                </label>
                                <div class="relative">
                                    <input type="number" 
                                           wire:model="pass_threshold"
                                           min="1" max="100"
                                           class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400">%</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">可決に必要な賛成票の割合</p>
                            </div>
                        @endif
                    </div>

                    {{-- Options --}}
                    <div class="space-y-4">
                        <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" 
                                   wire:model="allow_anonymous_voting"
                                   class="rounded text-coop-600 focus:ring-coop-500">
                            <div class="ml-3">
                                <span class="block font-medium text-gray-900">{{ __('decisions.create.anonymous_voting_label') }}</span>
                                <span class="block text-sm text-gray-500">投票者の名前を非公開にできます</span>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" 
                                   wire:model="show_results_during_voting"
                                   class="rounded text-coop-600 focus:ring-coop-500">
                            <div class="ml-3">
                                <span class="block font-medium text-gray-900">{{ __('decisions.create.show_results_label') }}</span>
                                <span class="block text-sm text-gray-500">投票中も途中結果を表示します</span>
                            </div>
                        </label>
                    </div>
                </div>
            @endif

            {{-- Step 3: Participants --}}
            @if($currentStep === 3)
                <div class="space-y-6">
                    <div>
                        <p class="text-sm text-gray-500 mb-6">{{ $stepTitles[3]['subtitle'] }}</p>
                    </div>

                    {{-- Allowed Roles --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            {{ __('decisions.create.allowed_roles_label') }}
                        </label>
                        <div class="flex flex-wrap gap-3">
                            @foreach($roles as $key => $role)
                                <button type="button"
                                        wire:click="toggleRole('{{ $key }}')"
                                        class="px-4 py-2 rounded-lg border transition-colors
                                               {{ in_array($key, $allowed_roles) 
                                                  ? 'bg-coop-100 border-coop-500 text-coop-700' 
                                                  : 'bg-white border-gray-300 text-gray-700 hover:border-gray-400' }}">
                                    {{ $role['name'] }}
                                </button>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            @if(empty($allowed_roles))
                                {{ __('decisions.labels.all_roles') }}が参加できます
                            @else
                                選択した役割のメンバーのみ参加できます
                            @endif
                        </p>
                    </div>

                    {{-- Invite Only Toggle --}}
                    <div>
                        <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" 
                                   wire:model.live="is_invite_only"
                                   class="rounded text-coop-600 focus:ring-coop-500">
                            <div class="ml-3">
                                <span class="block font-medium text-gray-900">{{ __('decisions.create.invite_only_label') }}</span>
                                <span class="block text-sm text-gray-500">特定のユーザーのみ招待します</span>
                            </div>
                        </label>
                    </div>

                    {{-- User Selection (if invite only) --}}
                    @if($is_invite_only)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('decisions.create.select_users_label') }}
                            </label>
                            
                            {{-- Search --}}
                            <div class="relative mb-4">
                                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                <input type="text" 
                                       wire:model.live.debounce.300ms="userSearch"
                                       placeholder="{{ __('decisions.participants.search_placeholder') }}"
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500">
                            </div>

                            {{-- Selected Users --}}
                            @if($selectedUsers->isNotEmpty())
                                <div class="mb-4">
                                    <p class="text-sm text-gray-500 mb-2">選択済み ({{ $selectedUsers->count() }}人)</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($selectedUsers as $user)
                                            <span class="inline-flex items-center px-3 py-1 bg-coop-100 text-coop-700 rounded-full text-sm">
                                                {{ $user->name }}
                                                <button type="button" wire:click="toggleUser({{ $user->id }})" class="ml-2">
                                                    <x-heroicon-o-x-mark class="w-4 h-4" />
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Available Users --}}
                            <div class="border border-gray-200 rounded-lg max-h-60 overflow-y-auto">
                                @forelse($availableUsers as $user)
                                    <label class="flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0">
                                        <input type="checkbox" 
                                               wire:click="toggleUser({{ $user->id }})"
                                               {{ in_array($user->id, $invited_user_ids) ? 'checked' : '' }}
                                               class="rounded text-coop-600 focus:ring-coop-500">
                                        <div class="ml-3">
                                            <span class="block text-sm font-medium text-gray-900">{{ $user->name }}</span>
                                            <span class="block text-xs text-gray-500">{{ $user->email }}</span>
                                        </div>
                                        <span class="ml-auto text-xs text-gray-400">{{ $user->role }}</span>
                                    </label>
                                @empty
                                    <p class="p-4 text-sm text-gray-500 text-center">ユーザーが見つかりません</p>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Step 4: Documents & Timeline --}}
            @if($currentStep === 4)
                <div class="space-y-6">
                    <div>
                        <p class="text-sm text-gray-500 mb-6">{{ $stepTitles[4]['subtitle'] }}</p>
                    </div>

                    {{-- File Upload --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('decisions.create.upload_files_label') }}
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                            <input type="file" 
                                   wire:model="uploadedFiles"
                                   multiple
                                   class="hidden"
                                   id="file-upload">
                            <label for="file-upload" class="cursor-pointer">
                                <x-heroicon-o-cloud-arrow-up class="w-10 h-10 mx-auto text-gray-400" />
                                <p class="mt-2 text-sm text-gray-600">クリックしてファイルを選択</p>
                                <p class="text-xs text-gray-400 mt-1">最大10MB</p>
                            </label>
                        </div>

                        {{-- Uploaded Files List --}}
                        @if(count($uploadedFiles) > 0)
                            <div class="mt-4 space-y-2">
                                @foreach($uploadedFiles as $index => $file)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <x-heroicon-o-document class="w-5 h-5 text-gray-400 mr-3" />
                                            <span class="text-sm text-gray-700">{{ $file->getClientOriginalName() }}</span>
                                        </div>
                                        <button type="button" wire:click="removeUploadedFile({{ $index }})" class="text-red-500 hover:text-red-700">
                                            <x-heroicon-o-trash class="w-5 h-5" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- External Links --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('decisions.create.external_links_label') }}
                        </label>
                        
                        @foreach($external_links as $index => $link)
                            <div class="flex gap-2 mb-2">
                                <input type="text" 
                                       wire:model="external_links.{{ $index }}.title"
                                       placeholder="{{ __('decisions.documents.link_title_placeholder') }}"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500">
                                <input type="url" 
                                       wire:model="external_links.{{ $index }}.url"
                                       placeholder="{{ __('decisions.documents.url_placeholder') }}"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500">
                                <button type="button" wire:click="removeExternalLink({{ $index }})" class="text-red-500 hover:text-red-700 px-2">
                                    <x-heroicon-o-trash class="w-5 h-5" />
                                </button>
                            </div>
                        @endforeach

                        <button type="button" 
                                wire:click="addExternalLink"
                                class="mt-2 flex items-center text-sm text-coop-600 hover:text-coop-700">
                            <x-heroicon-o-plus class="w-4 h-4 mr-1" />
                            {{ __('decisions.actions.add_link') }}
                        </button>
                    </div>

                    {{-- Deadlines --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('decisions.create.feedback_deadline_label') }}
                            </label>
                            <input type="datetime-local" 
                                   wire:model="feedback_deadline"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500">
                            @error('feedback_deadline') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('decisions.create.voting_deadline_label') }}
                            </label>
                            <input type="datetime-local" 
                                   wire:model="voting_deadline"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500">
                            @error('voting_deadline') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Bottom Navigation --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4">
        <div class="max-w-3xl mx-auto flex justify-between items-center">
            <div>
                @if($currentStep > 1)
                    <button type="button" 
                            wire:click="previousStep"
                            class="px-4 py-2 text-gray-600 hover:text-gray-900">
                        {{ __('decisions.create.previous') }}
                    </button>
                @endif
            </div>

            <div class="flex gap-3">
                @if($currentStep === $totalSteps)
                    <button type="button" 
                            wire:click="saveDraft"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        {{ __('decisions.actions.save_draft') }}
                    </button>
                    <button type="button" 
                            wire:click="submit"
                            class="px-6 py-2 bg-coop-600 text-white rounded-lg hover:bg-coop-700">
                        {{ __('decisions.actions.publish') }}
                    </button>
                @else
                    <button type="button" 
                            wire:click="nextStep"
                            class="px-6 py-2 bg-coop-600 text-white rounded-lg hover:bg-coop-700">
                        {{ __('decisions.create.next') }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
