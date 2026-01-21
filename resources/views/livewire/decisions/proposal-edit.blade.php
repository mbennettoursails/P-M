<div class="min-h-screen bg-gray-50 pb-20">
    {{-- Header --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ __('decisions.actions.edit') }}</h1>
                    <p class="text-sm text-gray-500">{{ $proposal->title }}</p>
                </div>
                <button wire:click="cancel" class="text-gray-400 hover:text-gray-600">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="space-y-6">
            {{-- Basic Info --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('decisions.create.step1_title') }}</h2>
                
                <div class="space-y-4">
                    {{-- Title --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('decisions.create.title_label') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               wire:model="title"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500 focus:border-transparent">
                        @error('title') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Title English --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('decisions.create.title_en_label') }}
                        </label>
                        <input type="text" 
                               wire:model="title_en"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500 focus:border-transparent">
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('decisions.create.description_label') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea wire:model="description"
                                  rows="6"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500 focus:border-transparent"></textarea>
                        @error('description') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Description English --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('decisions.create.description_en_label') }}
                        </label>
                        <textarea wire:model="description_en"
                                  rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500 focus:border-transparent"></textarea>
                    </div>
                </div>
            </div>

            {{-- Decision Configuration --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('decisions.create.step2_title') }}</h2>
                
                <div class="space-y-4">
                    {{-- Decision Type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            {{ __('decisions.create.decision_type_label') }}
                        </label>
                        <div class="space-y-3 {{ !$canChangeDecisionType ? 'opacity-60' : '' }}">
                            @foreach($decisionTypes as $key => $type)
                                <label class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors
                                              {{ $decision_type === $key ? 'border-coop-500 bg-coop-50' : 'border-gray-200 hover:border-gray-300' }}
                                              {{ !$canChangeDecisionType ? 'pointer-events-none' : '' }}">
                                    <input type="radio" 
                                           wire:model.live="decision_type" 
                                           value="{{ $key }}"
                                           {{ !$canChangeDecisionType ? 'disabled' : '' }}
                                           class="mt-1 text-coop-600 focus:ring-coop-500">
                                    <div class="ml-3">
                                        <span class="block font-medium text-gray-900">{{ $type['name_ja'] }}</span>
                                        <span class="block text-sm text-gray-500 mt-1">{{ $type['description_ja'] }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @if(!$canChangeDecisionType)
                            <p class="text-xs text-amber-600 mt-2">決定方法は下書き段階でのみ変更できます</p>
                        @endif
                    </div>

                    {{-- Quorum & Threshold --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
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
                            </div>
                        @endif
                    </div>

                    {{-- Options --}}
                    <div class="space-y-4 mt-6">
                        <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" 
                                   wire:model="allow_anonymous_voting"
                                   class="rounded text-coop-600 focus:ring-coop-500">
                            <div class="ml-3">
                                <span class="block font-medium text-gray-900">{{ __('decisions.create.anonymous_voting_label') }}</span>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" 
                                   wire:model="show_results_during_voting"
                                   class="rounded text-coop-600 focus:ring-coop-500">
                            <div class="ml-3">
                                <span class="block font-medium text-gray-900">{{ __('decisions.create.show_results_label') }}</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Deadlines --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">期限設定</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('decisions.labels.feedback_deadline') }}
                        </label>
                        <input type="datetime-local" 
                               wire:model="feedback_deadline"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('decisions.labels.voting_deadline') }}
                        </label>
                        <input type="datetime-local" 
                               wire:model="voting_deadline"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500">
                    </div>
                </div>
            </div>

            {{-- Existing Documents --}}
            @if($proposal->documents->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">既存の資料</h2>
                    
                    <div class="space-y-2">
                        @foreach($proposal->documents as $document)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <x-dynamic-component :component="'heroicon-o-' . $document->icon" 
                                                         class="w-5 h-5 text-{{ $document->icon_color }}-500 mr-3" />
                                    <span class="text-sm text-gray-700">{{ $document->title }}</span>
                                </div>
                                <a href="{{ $document->url }}" target="_blank" class="text-coop-600 hover:text-coop-700">
                                    <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mt-2">資料の削除は詳細ページから行えます</p>
                </div>
            @endif

            {{-- Add New Documents --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">新しい資料を追加</h2>
                
                {{-- File Upload --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">ファイル</label>
                    <input type="file" 
                           wire:model="newDocuments"
                           multiple
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-coop-50 file:text-coop-700 hover:file:bg-coop-100">
                </div>

                {{-- External Links --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">外部リンク</label>
                    
                    @foreach($externalLinks as $index => $link)
                        <div class="flex gap-2 mb-2">
                            <input type="text" 
                                   wire:model="externalLinks.{{ $index }}.title"
                                   placeholder="タイトル"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500">
                            <input type="url" 
                                   wire:model="externalLinks.{{ $index }}.url"
                                   placeholder="https://..."
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
                        リンクを追加
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Actions --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4">
        <div class="max-w-3xl mx-auto flex justify-end gap-3">
            <button wire:click="cancel"
                    class="px-4 py-2 text-gray-700 hover:text-gray-900">
                {{ __('decisions.actions.cancel') }}
            </button>
            <button wire:click="save"
                    class="px-6 py-2 bg-coop-600 text-white rounded-lg hover:bg-coop-700">
                保存する
            </button>
        </div>
    </div>
</div>
