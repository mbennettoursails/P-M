<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('記事を作成') }}</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('知識倉庫に新しい記事を追加します') }}</p>
            </div>
            <a href="{{ route('knowledge.index') }}" wire:navigate class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <form wire:submit="save">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Main Content --}}
                    <div class="lg:col-span-2 space-y-6">
                        {{-- Title --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('タイトル') }} <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="title"
                                wire:model="title"
                                placeholder="{{ __('記事のタイトルを入力...') }}"
                                class="w-full px-4 py-3 text-lg border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                            @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Content Editor --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('本文') }} <span class="text-red-500">*</span>
                                </label>
                                <button type="button" wire:click="togglePreview" class="text-sm text-green-600 dark:text-green-400 hover:underline">
                                    {{ $showPreview ? __('編集に戻る') : __('プレビュー') }}
                                </button>
                            </div>

                            @if($showPreview)
                                <div class="prose prose-sm dark:prose-invert max-w-none border border-gray-200 dark:border-gray-700 rounded-lg p-4 min-h-[300px] bg-gray-50 dark:bg-gray-900">
                                    {!! $content !!}
                                </div>
                            @else
                                <livewire:components.tiptap-editor 
                                    wire:model="content"
                                    :content="$content"
                                    :placeholder="__('記事の本文を入力...')"
                                    min-height="300px"
                                />
                            @endif
                            @error('content') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Excerpt --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <div class="flex items-center justify-between mb-2">
                                <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('抜粋') }}</label>
                                <button type="button" wire:click="generateExcerpt" class="text-sm text-green-600 dark:text-green-400 hover:underline">
                                    {{ __('本文から自動生成') }}
                                </button>
                            </div>
                            <textarea 
                                id="excerpt"
                                wire:model="excerpt"
                                rows="3"
                                placeholder="{{ __('記事の要約を入力...') }}"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            ></textarea>
                        </div>

                        {{-- Tags --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('タグ') }}</label>
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach($tags as $index => $tag)
                                    <span class="inline-flex items-center px-2 py-1 bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200 rounded text-sm">
                                        #{{ $tag }}
                                        <button type="button" wire:click="removeTag({{ $index }})" class="ml-1 text-green-600 hover:text-green-800">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </span>
                                @endforeach
                            </div>
                            <div class="flex gap-2">
                                <input 
                                    type="text" 
                                    wire:model="newTag"
                                    wire:keydown.enter.prevent="addTag"
                                    placeholder="{{ __('タグを入力してEnter') }}"
                                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                >
                                <button type="button" wire:click="addTag" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                    {{ __('追加') }}
                                </button>
                            </div>
                        </div>

                        {{-- External Link (conditional) --}}
                        @if($type === 'external_link')
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">{{ __('外部リンク設定') }}</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="externalUrl" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('URL') }} <span class="text-red-500">*</span></label>
                                        <input 
                                            type="url" 
                                            id="externalUrl"
                                            wire:model="externalUrl"
                                            placeholder="https://..."
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                                        >
                                        @error('externalUrl') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="externalSource" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('出典') }}</label>
                                        <input 
                                            type="text" 
                                            id="externalSource"
                                            wire:model="externalSource"
                                            placeholder="{{ __('例: 生活クラブ本部') }}"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                                        >
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- File Attachments --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">{{ __('添付ファイル') }}</label>
                            
                            @if(count($attachments) > 0)
                                <div class="space-y-2 mb-4">
                                    @foreach($attachments as $index => $file)
                                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                            <span class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ $file->getClientOriginalName() }}</span>
                                            <button type="button" wire:click="removeAttachment({{ $index }})" class="text-red-500 hover:text-red-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                                <input type="file" wire:model="attachments" multiple class="hidden" id="attachments">
                                <label for="attachments" class="cursor-pointer">
                                    <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('クリックでファイルを追加') }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ __('最大10MB/ファイル') }}</p>
                                </label>
                            </div>
                            <div wire:loading wire:target="attachments" class="mt-2 text-sm text-gray-500">{{ __('アップロード中...') }}</div>
                        </div>
                    </div>

                    {{-- Sidebar --}}
                    <div class="lg:col-span-1 space-y-6">
                        {{-- Category & Type --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">{{ __('分類') }}</h3>
                            
                            <div class="mb-4">
                                <label for="categoryId" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('カテゴリ') }} <span class="text-red-500">*</span></label>
                                <select wire:model="categoryId" id="categoryId" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    @foreach($this->categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="type" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('種類') }} <span class="text-red-500">*</span></label>
                                <select wire:model.live="type" id="type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    @foreach($this->types as $typeKey => $typeLabel)
                                        <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Options --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">{{ __('オプション') }}</h3>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="isFeatured" class="w-4 h-4 text-green-600 rounded">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('注目記事にする') }}</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="isPinned" class="w-4 h-4 text-green-600 rounded">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('ピン留めする') }}</span>
                                </label>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-3">
                            <button type="button" wire:click="publish" wire:loading.attr="disabled" class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white font-medium rounded-lg transition flex items-center justify-center">
                                <svg wire:loading.remove wire:target="publish" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <svg wire:loading wire:target="publish" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                {{ __('公開する') }}
                            </button>

                            <button type="button" wire:click="saveDraft" wire:loading.attr="disabled" class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition">
                                {{ __('下書き保存') }}
                            </button>

                            <a href="{{ route('knowledge.index') }}" wire:navigate class="block w-full px-4 py-3 text-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">
                                {{ __('キャンセル') }}
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
