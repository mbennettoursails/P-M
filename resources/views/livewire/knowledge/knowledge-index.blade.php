<div>
    {{-- Page Header --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('知識倉庫') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('よくある質問、ガイド、レシピなど') }}
                </p>
            </div>
            
            @if($this->canManage)
                <a 
                    href="{{ route('knowledge.create') }}" 
                    wire:navigate
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition shadow-sm"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ __('新規作成') }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Search Bar --}}
            <div class="mb-6">
                <div class="relative max-w-2xl mx-auto">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('キーワードで検索...') }}"
                        class="w-full pl-12 pr-4 py-3 text-lg border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent shadow-sm"
                    >
                    @if($search)
                        <button 
                            wire:click="$set('search', '')"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Featured Articles (only when no filters) --}}
            @if(!$search && !$category && !$type && !$tag && $this->featuredArticles->isNotEmpty())
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('注目の記事') }}</h3>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($this->featuredArticles as $featured)
                            <a 
                                href="{{ $featured->is_external ? $featured->external_url : route('knowledge.show', $featured) }}"
                                @if($featured->is_external) target="_blank" rel="noopener" @else wire:navigate @endif
                                class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 hover:shadow-md transition group"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="p-2 bg-green-100 dark:bg-green-800 rounded-lg">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-medium text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 line-clamp-2">
                                            {{ $featured->display_title }}
                                            @if($featured->is_external)
                                                <svg class="inline w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                            @endif
                                        </h4>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $featured->category->display_name }}</p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex flex-col lg:flex-row gap-6">
                {{-- Sidebar: Categories --}}
                <aside class="lg:w-64 flex-shrink-0">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sticky top-4">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-3">{{ __('カテゴリ') }}</h3>
                        <nav class="space-y-1">
                            <button 
                                wire:click="$set('category', '')"
                                class="w-full flex items-center justify-between px-3 py-2 text-sm rounded-lg transition {{ !$category ? 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                            >
                                <span>{{ __('すべて') }}</span>
                            </button>
                            @foreach($this->categories as $cat)
                                <button 
                                    wire:click="selectCategory('{{ $cat->uuid }}')"
                                    class="w-full flex items-center justify-between px-3 py-2 text-sm rounded-lg transition {{ $category === $cat->uuid ? 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                                >
                                    <span class="flex items-center">
                                        <span class="w-2 h-2 rounded-full {{ $cat->color_classes }} mr-2"></span>
                                        {{ $cat->display_name }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $cat->published_articles_count }}</span>
                                </button>
                            @endforeach
                        </nav>

                        {{-- Type Filter --}}
                        <h3 class="font-semibold text-gray-900 dark:text-white mt-6 mb-3">{{ __('種類') }}</h3>
                        <nav class="space-y-1">
                            <button 
                                wire:click="$set('type', '')"
                                class="w-full text-left px-3 py-2 text-sm rounded-lg transition {{ !$type ? 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                            >
                                {{ __('すべて') }}
                            </button>
                            @foreach($this->types as $typeKey => $typeLabel)
                                <button 
                                    wire:click="$set('type', '{{ $typeKey }}')"
                                    class="w-full text-left px-3 py-2 text-sm rounded-lg transition {{ $type === $typeKey ? 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                                >
                                    {{ $typeLabel }}
                                </button>
                            @endforeach
                        </nav>

                        {{-- Popular Tags --}}
                        @if($this->popularTags)
                            <h3 class="font-semibold text-gray-900 dark:text-white mt-6 mb-3">{{ __('人気のタグ') }}</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($this->popularTags as $tagName)
                                    <button 
                                        wire:click="selectTag('{{ $tagName }}')"
                                        class="px-2 py-1 text-xs rounded-full transition {{ $tag === $tagName ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
                                    >
                                        {{ $tagName }}
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        {{-- Clear Filters --}}
                        @if($search || $category || $type || $tag)
                            <button 
                                wire:click="clearFilters"
                                class="w-full mt-4 px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"
                            >
                                {{ __('フィルターをクリア') }}
                            </button>
                        @endif
                    </div>
                </aside>

                {{-- Main Content: Articles --}}
                <main class="flex-1 min-w-0">
                    {{-- Active Filters Display --}}
                    @if($search || $category || $type || $tag)
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('フィルター:') }}</span>
                            @if($search)
                                <span class="inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-sm">
                                    "{{ $search }}"
                                    <button wire:click="$set('search', '')" class="ml-1 text-gray-500 hover:text-gray-700">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </span>
                            @endif
                            @if($this->selectedCategory)
                                <span class="inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-sm">
                                    {{ $this->selectedCategory->display_name }}
                                    <button wire:click="$set('category', '')" class="ml-1 text-gray-500 hover:text-gray-700">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </span>
                            @endif
                            @if($type)
                                <span class="inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-sm">
                                    {{ $this->types[$type] ?? $type }}
                                    <button wire:click="$set('type', '')" class="ml-1 text-gray-500 hover:text-gray-700">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </span>
                            @endif
                            @if($tag)
                                <span class="inline-flex items-center px-2 py-1 bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200 rounded text-sm">
                                    #{{ $tag }}
                                    <button wire:click="$set('tag', '')" class="ml-1 text-green-600 hover:text-green-800">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </span>
                            @endif
                        </div>
                    @endif

                    {{-- Articles Grid --}}
                    @if($this->articles->isNotEmpty())
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach($this->articles as $article)
                                <article 
                                    wire:key="article-{{ $article->id }}"
                                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition group"
                                >
                                    <a 
                                        href="{{ $article->is_external ? $article->external_url : route('knowledge.show', $article) }}"
                                        @if($article->is_external) target="_blank" rel="noopener" @else wire:navigate @endif
                                        class="block p-4"
                                    >
                                        <div class="flex items-start gap-3">
                                            {{-- Type Icon --}}
                                            <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg flex-shrink-0">
                                                @if($article->type === 'faq')
                                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                @elseif($article->type === 'recipe')
                                                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                                                    </svg>
                                                @elseif($article->type === 'guide')
                                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                    </svg>
                                                @elseif($article->type === 'external_link')
                                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                @endif
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                {{-- Badges --}}
                                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                                    @if($article->is_pinned)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-200">
                                                            {{ __('ピン留め') }}
                                                        </span>
                                                    @endif
                                                    <span class="text-xs {{ $article->category->color_classes }} px-1.5 py-0.5 rounded">
                                                        {{ $article->category->display_name }}
                                                    </span>
                                                </div>

                                                {{-- Title --}}
                                                <h3 class="font-medium text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition line-clamp-2">
                                                    {{ $article->display_title }}
                                                    @if($article->is_external)
                                                        <svg class="inline w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                        </svg>
                                                    @endif
                                                </h3>

                                                {{-- Excerpt --}}
                                                @if($article->excerpt)
                                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                                        {{ $article->excerpt }}
                                                    </p>
                                                @endif

                                                {{-- Tags --}}
                                                @if($article->tags && count($article->tags) > 0)
                                                    <div class="mt-2 flex flex-wrap gap-1">
                                                        @foreach(array_slice($article->tags, 0, 3) as $articleTag)
                                                            <span class="text-xs text-gray-500 dark:text-gray-400">#{{ $articleTag }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                {{-- Meta --}}
                                                <div class="mt-2 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                                    <span>{{ $article->type_label }}</span>
                                                    @if($article->helpful_percentage !== null)
                                                        <span class="flex items-center">
                                                            <svg class="w-3 h-3 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"></path>
                                                            </svg>
                                                            {{ $article->helpful_percentage }}%
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        @if($this->articles->hasPages())
                            <div class="mt-6">
                                {{ $this->articles->links() }}
                            </div>
                        @endif
                    @else
                        {{-- Empty State --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                {{ __('記事が見つかりません') }}
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-6">
                                @if($search || $category || $type || $tag)
                                    {{ __('検索条件に一致する記事がありませんでした。') }}
                                @else
                                    {{ __('まだ記事が投稿されていません。') }}
                                @endif
                            </p>
                            @if($search || $category || $type || $tag)
                                <button 
                                    wire:click="clearFilters"
                                    class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition"
                                >
                                    {{ __('フィルターをクリア') }}
                                </button>
                            @endif
                        </div>
                    @endif
                </main>
            </div>
        </div>
    </div>
</div>
