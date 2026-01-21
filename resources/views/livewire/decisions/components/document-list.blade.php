<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('decisions.documents.title') }}</h2>
        
        @if($canUpload)
            <div class="flex gap-2">
                <button wire:click="openLinkModal"
                        class="text-sm text-coop-600 hover:text-coop-700">
                    <x-heroicon-o-link class="w-4 h-4 inline mr-1" />
                    {{ __('decisions.actions.add_link') }}
                </button>
                <button wire:click="openUploadModal"
                        class="text-sm text-coop-600 hover:text-coop-700">
                    <x-heroicon-o-arrow-up-tray class="w-4 h-4 inline mr-1" />
                    {{ __('decisions.actions.upload') }}
                </button>
            </div>
        @endif
    </div>

    @if($documents->isEmpty())
        <div class="text-center py-8 text-gray-500">
            <x-heroicon-o-document class="w-12 h-12 mx-auto mb-3 text-gray-300" />
            <p>{{ __('decisions.documents.no_documents') }}</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($documents as $document)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <a href="{{ $document->url }}" 
                       target="_blank"
                       rel="noopener noreferrer"
                       class="flex items-center flex-1 min-w-0">
                        {{-- Icon --}}
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-{{ $document->icon_color }}-100 flex items-center justify-center">
                            <x-dynamic-component :component="'heroicon-o-' . $document->icon" 
                                                 class="w-5 h-5 text-{{ $document->icon_color }}-600" />
                        </div>
                        
                        {{-- Info --}}
                        <div class="ml-4 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $document->localized_title }}
                            </p>
                            <div class="flex items-center text-xs text-gray-500 mt-1">
                                <span>{{ $document->type_name }}</span>
                                @if($document->file_size_formatted)
                                    <span class="mx-2">•</span>
                                    <span>{{ $document->file_size_formatted }}</span>
                                @endif
                                @if($document->is_external)
                                    <span class="mx-2">•</span>
                                    <span class="text-purple-600">{{ $document->external_domain }}</span>
                                @endif
                            </div>
                        </div>
                    </a>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 ml-4">
                        <a href="{{ $document->url }}" 
                           target="_blank"
                           class="p-2 text-gray-400 hover:text-gray-600 rounded">
                            <x-heroicon-o-arrow-top-right-on-square class="w-5 h-5" />
                        </a>
                        
                        @if($document->canBeDeletedBy(auth()->user()))
                            <button wire:click="deleteDocument({{ $document->id }})"
                                    wire:confirm="この資料を削除しますか？"
                                    class="p-2 text-gray-400 hover:text-red-600 rounded">
                                <x-heroicon-o-trash class="w-5 h-5" />
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Upload Modal --}}
    @if($showUploadModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeUploadModal"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('decisions.documents.upload') }}
                    </h3>
                    
                    <div class="space-y-4">
                        {{-- File Input --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ファイル</label>
                            <input type="file" 
                                   wire:model="uploadFile"
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-coop-50 file:text-coop-700 hover:file:bg-coop-100">
                            @error('uploadFile') 
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Title --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('decisions.documents.title_placeholder') }}
                            </label>
                            <input type="text" 
                                   wire:model="uploadTitle"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button wire:click="closeUploadModal"
                                class="px-4 py-2 text-gray-700 hover:text-gray-900">
                            {{ __('decisions.actions.cancel') }}
                        </button>
                        <button wire:click="uploadDocument"
                                class="px-4 py-2 bg-coop-600 text-white rounded-lg hover:bg-coop-700">
                            {{ __('decisions.actions.upload') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Link Modal --}}
    @if($showLinkModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeLinkModal"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('decisions.documents.add_link') }}
                    </h3>
                    
                    <div class="space-y-4">
                        {{-- Title --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('decisions.documents.link_title_placeholder') }}
                            </label>
                            <input type="text" 
                                   wire:model="linkTitle"
                                   placeholder="例: 企画書 Google Docs"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500">
                            @error('linkTitle') 
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- URL --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">URL</label>
                            <input type="url" 
                                   wire:model="linkUrl"
                                   placeholder="{{ __('decisions.documents.url_placeholder') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-coop-500">
                            @error('linkUrl') 
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button wire:click="closeLinkModal"
                                class="px-4 py-2 text-gray-700 hover:text-gray-900">
                            {{ __('decisions.actions.cancel') }}
                        </button>
                        <button wire:click="addLink"
                                class="px-4 py-2 bg-coop-600 text-white rounded-lg hover:bg-coop-700">
                            {{ __('decisions.actions.add_link') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
