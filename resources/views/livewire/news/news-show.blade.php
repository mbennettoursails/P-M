<div>
    {{-- Page Header --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <nav class="flex items-center space-x-2 text-sm">
                <a href="{{ route('news.index') }}" wire:navigate class="text-gray-500 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400">
                    {{ __('ニュース') }}
                </a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-gray-900 dark:text-white font-medium truncate max-w-xs">{{ $news->title }}</span>
            </nav>
            
            @if($this->canEdit)
                <div class="flex items-center space-x-2">
                    <a 
                        href="{{ route('news.edit', $news) }}"
                        wire:navigate
                        class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition"
                        title="{{ __('編集') }}"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Draft/Archived Warning --}}
            @if($news->status !== 'published')
                <div class="mb-6 p-4 rounded-lg {{ $news->status === 'draft' ? 'bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800' : 'bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700' }}">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 {{ $news->status === 'draft' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400' }} mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="{{ $news->status === 'draft' ? 'text-blue-800 dark:text-blue-200' : 'text-gray-800 dark:text-gray-200' }} font-medium">
                            {{ $news->status === 'draft' ? __('この記事は下書きです。公開されていません。') : __('この記事はアーカイブされています。') }}
                        </span>
                    </div>
                    
                    @if($this->canEdit && $news->status === 'draft')
                        <div class="mt-3">
                            <button 
                                wire:click="publish"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition"
                            >
                                {{ __('公開する') }}
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Article --}}
            <article class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                {{-- Featured Image --}}
                @if($news->featured_image)
                    <div class="aspect-video">
                        <img 
                            src="{{ $news->featured_image }}" 
                            alt="{{ $news->featured_image_alt ?? $news->title }}"
                            class="w-full h-full object-cover"
                        >
                    </div>
                @endif

                <div class="p-6 sm:p-8">
                    {{-- Badges --}}
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        @if($news->is_pinned)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-200">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"></path>
                                    <path fill-rule="evenodd" d="M10 18a1 1 0 01-1-1v-6H5a1 1 0 110-2h10a1 1 0 110 2h-4v6a1 1 0 01-1 1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ __('ピン留め') }}
                            </span>
                        @endif

                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            {{ $news->category === 'urgent' ? 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200' }}">
                            {{ $news->category_label }}
                        </span>

                        @if($news->status !== 'published')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200">
                                {{ $news->status_label }}
                            </span>
                        @endif
                    </div>

                    {{-- Title --}}
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-4">
                        {{ $news->title }}
                    </h1>

                    {{-- Meta --}}
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400 mb-8 pb-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-2">
                                <span class="text-green-700 dark:text-green-300 font-bold text-sm">
                                    {{ mb_substr($news->author->name ?? 'U', 0, 1) }}
                                </span>
                            </div>
                            <span>{{ $news->author->name }}</span>
                        </div>
                        <span>{{ $news->published_date ?? $news->created_at->format('Y年n月j日') }}</span>
                        <span>{{ __(':min分で読めます', ['min' => $news->reading_time]) }}</span>
                        @if($this->canEdit)
                            <span>{{ __(':count回閲覧', ['count' => number_format($news->view_count)]) }}</span>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="prose prose-lg dark:prose-invert max-w-none">
                        {!! $news->content !!}
                    </div>
                </div>
            </article>

            {{-- Actions (Managers Only) --}}
            @if($this->canEdit)
                <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('管理者アクション') }}</h3>
                    <div class="flex flex-wrap gap-2">
                        <a 
                            href="{{ route('news.edit', $news) }}"
                            wire:navigate
                            class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            {{ __('編集') }}
                        </a>

                        <button 
                            wire:click="togglePinned"
                            class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                            </svg>
                            {{ $news->is_pinned ? __('ピン解除') : __('ピン留め') }}
                        </button>

                        @if($news->status === 'published')
                            <button 
                                wire:click="unpublish"
                                class="inline-flex items-center px-3 py-2 bg-yellow-100 dark:bg-yellow-900/30 hover:bg-yellow-200 dark:hover:bg-yellow-900/50 text-yellow-800 dark:text-yellow-200 text-sm font-medium rounded-lg transition"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                                {{ __('非公開にする') }}
                            </button>
                        @else
                            <button 
                                wire:click="publish"
                                class="inline-flex items-center px-3 py-2 bg-green-100 dark:bg-green-900/30 hover:bg-green-200 dark:hover:bg-green-900/50 text-green-800 dark:text-green-200 text-sm font-medium rounded-lg transition"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                {{ __('公開する') }}
                            </button>
                        @endif

                        @if($this->canDelete)
                            <button 
                                wire:click="delete"
                                wire:confirm="{{ __('この記事を削除してもよろしいですか？') }}"
                                class="inline-flex items-center px-3 py-2 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-800 dark:text-red-200 text-sm font-medium rounded-lg transition"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                {{ __('削除') }}
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Related Articles --}}
            @if($this->relatedNews->isNotEmpty())
                <div class="mt-8">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('関連記事') }}
                    </h2>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($this->relatedNews as $related)
                            <a 
                                href="{{ route('news.show', $related) }}"
                                wire:navigate
                                wire:key="related-{{ $related->id }}"
                                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition group"
                            >
                                @if($related->featured_image)
                                    <div class="aspect-video">
                                        <img 
                                            src="{{ $related->featured_image }}" 
                                            alt="{{ $related->featured_image_alt ?? $related->title }}"
                                            class="w-full h-full object-cover"
                                        >
                                    </div>
                                @endif
                                <div class="p-4">
                                    <h3 class="font-medium text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition line-clamp-2">
                                        {{ $related->title }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $related->published_date }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Back Button --}}
            <div class="mt-8 text-center">
                <a 
                    href="{{ route('news.index') }}"
                    wire:navigate
                    class="inline-flex items-center text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 font-medium"
                >
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('ニュース一覧に戻る') }}
                </a>
            </div>
        </div>
    </div>
</div>
