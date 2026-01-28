<div>
    {{-- Page Header --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <nav class="flex items-center space-x-2 text-sm">
                <a href="{{ route('knowledge.index') }}" wire:navigate class="text-gray-500 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400">
                    {{ __('知識倉庫') }}
                </a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-gray-900 dark:text-white font-medium truncate max-w-xs">{{ $article->display_title }}</span>
            </nav>
            
            @if($this->canEdit)
                <a 
                    href="{{ route('knowledge.edit', $article) }}"
                    wire:navigate
                    class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Main Article --}}
            <article class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 sm:p-8">
                    {{-- Badges --}}
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $article->category->color_classes }}">
                            {{ $article->category->display_name }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                            {{ $article->type_label }}
                        </span>
                    </div>

                    {{-- Title --}}
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-4">
                        {{ $article->display_title }}
                    </h1>

                    {{-- Meta --}}
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400 mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                        <span>{{ __('更新日:') }} {{ $article->last_edited_at?->format('Y年n月j日') ?? $article->published_date }}</span>
                        <span>{{ __(':min分で読めます', ['min' => $article->reading_time]) }}</span>
                    </div>

                    {{-- Tags --}}
                    @if($article->tags && count($article->tags) > 0)
                        <div class="flex flex-wrap gap-2 mb-6">
                            @foreach($article->tags as $tag)
                                <a 
                                    href="{{ route('knowledge.index', ['tag' => $tag]) }}"
                                    wire:navigate
                                    class="inline-flex items-center px-2 py-1 text-sm bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                                >
                                    #{{ $tag }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    {{-- Content --}}
                    <div class="prose prose-lg dark:prose-invert max-w-none">
                        {!! $article->content !!}
                    </div>

                    {{-- Attachments --}}
                    @if($article->attachments->isNotEmpty())
                        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                {{ __('添付ファイル') }}
                            </h3>
                            <div class="space-y-2">
                                @foreach($article->attachments as $attachment)
                                    <a 
                                        href="{{ $attachment->download_url }}"
                                        class="flex items-center p-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition group"
                                    >
                                        <div class="p-2 bg-white dark:bg-gray-800 rounded-lg mr-3">
                                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-900 dark:text-white truncate">{{ $attachment->display_title }}</p>
                                            <p class="text-sm text-gray-500">{{ $attachment->type_label }} • {{ $attachment->human_size }}</p>
                                        </div>
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </article>

            {{-- Feedback Section --}}
            <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('この記事は役に立ちましたか？') }}
                </h3>
                <div class="flex items-center gap-4">
                    <button 
                        wire:click="markHelpful"
                        class="inline-flex items-center px-4 py-2 rounded-lg transition {{ $userFeedback === true ? 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200 ring-2 ring-green-500' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
                    >
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"></path>
                        </svg>
                        {{ __('役に立った') }}
                        @if($article->helpful_count > 0)
                            <span class="ml-2 text-sm">({{ $article->helpful_count }})</span>
                        @endif
                    </button>
                    <button 
                        wire:click="markNotHelpful"
                        class="inline-flex items-center px-4 py-2 rounded-lg transition {{ $userFeedback === false ? 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200 ring-2 ring-red-500' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
                    >
                        <svg class="w-5 h-5 mr-2 transform rotate-180" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"></path>
                        </svg>
                        {{ __('役に立たなかった') }}
                    </button>
                </div>
                @if($article->helpful_percentage !== null)
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                        {{ __(':percent%のユーザーがこの記事を役に立ったと評価しています', ['percent' => $article->helpful_percentage]) }}
                    </p>
                @endif
            </div>

            {{-- Related Articles --}}
            @if($this->relatedArticles->isNotEmpty() || $this->sameCategoryArticles->isNotEmpty())
                <div class="mt-8">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('関連記事') }}
                    </h2>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($this->relatedArticles->isNotEmpty() ? $this->relatedArticles : $this->sameCategoryArticles as $related)
                            <a 
                                href="{{ route('knowledge.show', $related) }}"
                                wire:navigate
                                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition group"
                            >
                                <span class="text-xs {{ $related->category->color_classes }} px-2 py-0.5 rounded">
                                    {{ $related->category->display_name }}
                                </span>
                                <h3 class="mt-2 font-medium text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 line-clamp-2">
                                    {{ $related->display_title }}
                                </h3>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Back Button --}}
            <div class="mt-8 text-center">
                <a 
                    href="{{ route('knowledge.index') }}"
                    wire:navigate
                    class="inline-flex items-center text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 font-medium"
                >
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('知識倉庫に戻る') }}
                </a>
            </div>
        </div>
    </div>
</div>
