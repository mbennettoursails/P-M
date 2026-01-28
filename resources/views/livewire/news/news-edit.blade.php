<div>
    {{-- Page Header --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('ニュースを編集') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 truncate max-w-md">
                    {{ $news->title }}
                </p>
            </div>
            <a 
                href="{{ route('news.show', $news) }}" 
                wire:navigate
                class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
            >
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
                    {{-- Main Content Area --}}
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
                            @error('title')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Content Editor --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('本文') }} <span class="text-red-500">*</span>
                                </label>
                                <button 
                                    type="button"
                                    wire:click="togglePreview"
                                    class="text-sm text-green-600 dark:text-green-400 hover:underline"
                                >
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
                                    :content-json="$contentJson"
                                    :placeholder="__('ニュースの本文を入力...')"
                                    min-height="300px"
                                    :autofocus="false"
                                />
                            @endif
                            
                            @error('content')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Excerpt --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <div class="flex items-center justify-between mb-2">
                                <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('抜粋') }}
                                </label>
                                <button 
                                    type="button"
                                    wire:click="generateExcerpt"
                                    class="text-sm text-green-600 dark:text-green-400 hover:underline"
                                >
                                    {{ __('本文から自動生成') }}
                                </button>
                            </div>
                            <textarea 
                                id="excerpt"
                                wire:model="excerpt"
                                rows="3"
                                placeholder="{{ __('記事の要約を入力（一覧に表示されます）') }}"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            ></textarea>
                            @error('excerpt')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Featured Image --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
                                {{ __('アイキャッチ画像') }}
                            </label>

                            @if($featuredImagePreview || $existingFeaturedImage)
                                <div class="relative mb-4">
                                    <img 
                                        src="{{ $featuredImagePreview ?? $existingFeaturedImage }}" 
                                        alt="{{ __('プレビュー') }}"
                                        class="w-full h-48 object-cover rounded-lg"
                                    >
                                    <button 
                                        type="button"
                                        wire:click="removeFeaturedImage"
                                        class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600 transition"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div>
                                    <label for="featuredImageAlt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ __('代替テキスト') }}
                                    </label>
                                    <input 
                                        type="text" 
                                        id="featuredImageAlt"
                                        wire:model="featuredImageAlt"
                                        placeholder="{{ __('画像の説明を入力...') }}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm"
                                    >
                                </div>
                            @else
                                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center hover:border-gray-400 dark:hover:border-gray-500 transition">
                                    <input 
                                        type="file" 
                                        wire:model="featuredImage"
                                        accept="image/*"
                                        class="hidden"
                                        id="featuredImage"
                                    >
                                    <label for="featuredImage" class="cursor-pointer">
                                        <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <p class="text-gray-600 dark:text-gray-400">{{ __('クリックで画像をアップロード') }}</p>
                                        <p class="text-sm text-gray-500 mt-1">{{ __('PNG, JPG, GIF (最大5MB)') }}</p>
                                    </label>
                                </div>
                            @endif

                            @error('featuredImage')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror

                            <div wire:loading wire:target="featuredImage" class="mt-2">
                                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                    <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ __('アップロード中...') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Sidebar --}}
                    <div class="lg:col-span-1 space-y-6">
                        {{-- Status Info --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('記事情報') }}</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">{{ __('ステータス') }}</dt>
                                    <dd class="font-medium {{ $news->status === 'published' ? 'text-green-600 dark:text-green-400' : 'text-blue-600 dark:text-blue-400' }}">
                                        {{ $news->status_label }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">{{ __('作成日') }}</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ $news->created_at->format('Y/m/d') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">{{ __('閲覧数') }}</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ number_format($news->view_count) }}</dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Publish Settings --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">{{ __('公開設定') }}</h3>

                            <div class="mb-4">
                                <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ __('カテゴリ') }}
                                </label>
                                <select 
                                    id="category"
                                    wire:model="category"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm"
                                >
                                    @foreach($this->categories as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="publishedAt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ __('公開日時') }}
                                </label>
                                <input 
                                    type="datetime-local" 
                                    id="publishedAt"
                                    wire:model="publishedAt"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm"
                                >
                            </div>

                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        wire:model="isPinned"
                                        class="w-4 h-4 text-green-600 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-green-500"
                                    >
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('ピン留めする') }}</span>
                                </label>

                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        wire:model="isFeatured"
                                        class="w-4 h-4 text-green-600 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-green-500"
                                    >
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('注目記事にする') }}</span>
                                </label>
                            </div>
                        </div>

                        {{-- Visibility Settings --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">{{ __('公開範囲') }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                {{ __('選択しない場合、全員に公開されます。') }}
                            </p>

                            <div class="space-y-2">
                                @foreach($this->availableRoles as $role => $label)
                                    <label class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            wire:model="visibleToRoles"
                                            value="{{ $role }}"
                                            class="w-4 h-4 text-green-600 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-green-500"
                                        >
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-3">
                            <button 
                                type="button"
                                wire:click="publish"
                                wire:loading.attr="disabled"
                                class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white font-medium rounded-lg transition flex items-center justify-center"
                            >
                                <svg wire:loading.remove wire:target="publish" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <svg wire:loading wire:target="publish" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('更新して公開') }}
                            </button>

                            <button 
                                type="button"
                                wire:click="saveDraft"
                                wire:loading.attr="disabled"
                                class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition flex items-center justify-center"
                            >
                                <svg wire:loading.remove wire:target="saveDraft" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                </svg>
                                <svg wire:loading wire:target="saveDraft" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('下書きとして保存') }}
                            </button>

                            <div class="flex gap-2">
                                <a 
                                    href="{{ route('news.show', $news) }}"
                                    wire:navigate
                                    class="flex-1 px-4 py-3 text-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border border-gray-300 dark:border-gray-600 rounded-lg transition"
                                >
                                    {{ __('キャンセル') }}
                                </a>
                                <a 
                                    href="{{ route('news.show', $news) }}"
                                    target="_blank"
                                    class="px-4 py-3 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border border-gray-300 dark:border-gray-600 rounded-lg transition"
                                    title="{{ __('プレビュー') }}"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
