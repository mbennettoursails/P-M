<div>
    {{-- Page Header --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('ニュース') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('北東京生活クラブからのお知らせ') }}
                </p>
            </div>
            
            @if($this->canManageNews)
                <a 
                    href="{{ route('news.create') }}" 
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
            
            {{-- Unread Badge --}}
            @if($this->unreadCount > 0)
                <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span class="text-sm font-medium text-green-800 dark:text-green-200">
                            {{ __(':count件の未読記事があります', ['count' => $this->unreadCount]) }}
                        </span>
                    </div>
                </div>
            @endif

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
                <div class="flex flex-col lg:flex-row gap-4">
                    {{-- Search --}}
                    <div class="flex-1">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="search"
                                placeholder="{{ __('キーワードで検索...') }}"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                        </div>
                    </div>

                    {{-- Category Filter --}}
                    <div class="w-full lg:w-48">
                        <select 
                            wire:model.live="category"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                            <option value="">{{ __('すべてのカテゴリ') }}</option>
                            @foreach($this->categories as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Filter (Managers Only) --}}
                    @if($this->canManageNews)
                        <div class="w-full lg:w-40">
                            <select 
                                wire:model.live="status"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                                @foreach($this->statuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- My Articles Toggle --}}
                        <button 
                            wire:click="toggleMyArticles"
                            class="px-4 py-2 border rounded-lg transition {{ $myArticlesOnly ? 'bg-green-100 dark:bg-green-900/50 border-green-300 dark:border-green-700 text-green-800 dark:text-green-200' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                        >
                            {{ __('自分の記事') }}
                        </button>
                    @endif

                    {{-- Clear Filters --}}
                    @if($search || $category || $status !== 'published' || $myArticlesOnly)
                        <button 
                            wire:click="clearFilters"
                            class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition"
                        >
                            {{ __('クリア') }}
                        </button>
                    @endif
                </div>
            </div>

            {{-- Category Pills (Mobile-friendly) --}}
            <div class="flex flex-wrap gap-2 mb-6 lg:hidden">
                <button 
                    wire:click="$set('category', '')"
                    class="px-3 py-1.5 text-sm rounded-full transition {{ !$category ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}"
                >
                    {{ __('すべて') }}
                </button>
                @foreach($this->categories as $value => $label)
                    <button 
                        wire:click="$set('category', '{{ $value }}')"
                        class="px-3 py-1.5 text-sm rounded-full transition {{ $category === $value ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- News List --}}
            <div class="space-y-4">
                @forelse($this->news as $article)
                    <article 
                        wire:key="news-{{ $article->id }}"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition group"
                    >
                        <a href="{{ route('news.show', $article) }}" wire:navigate class="block">
                            <div class="p-4 sm:p-6">
                                <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                                    {{-- Featured Image (if exists) --}}
                                    @if($article->featured_image)
                                        <div class="sm:w-32 sm:h-24 flex-shrink-0">
                                            <img 
                                                src="{{ $article->featured_image }}" 
                                                alt="{{ $article->featured_image_alt ?? $article->title }}"
                                                class="w-full h-48 sm:h-24 object-cover rounded-lg"
                                            >
                                        </div>
                                    @endif

                                    <div class="flex-1 min-w-0">
                                        {{-- Badges --}}
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            {{-- Pinned Badge --}}
                                            @if($article->is_pinned)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-200">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"></path>
                                                        <path fill-rule="evenodd" d="M10 18a1 1 0 01-1-1v-6H5a1 1 0 110-2h10a1 1 0 110 2h-4v6a1 1 0 01-1 1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ __('ピン留め') }}
                                                </span>
                                            @endif

                                            {{-- Category Badge --}}
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                                {{ $article->category === 'urgent' ? 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200' }}">
                                                {{ $article->category_label }}
                                            </span>

                                            {{-- Status Badge (Managers Only) --}}
                                            @if($this->canManageNews && $article->status !== 'published')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                                    {{ $article->status === 'draft' ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                                                    {{ $article->status_label }}
                                                </span>
                                            @endif

                                            {{-- Unread Indicator --}}
                                            @if(!$article->isReadBy(auth()->user()))
                                                <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                            @endif
                                        </div>

                                        {{-- Title --}}
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition line-clamp-2">
                                            {{ $article->title }}
                                        </h3>

                                        {{-- Excerpt --}}
                                        @if($article->excerpt)
                                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                                {{ $article->excerpt }}
                                            </p>
                                        @endif

                                        {{-- Meta --}}
                                        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                            <span>{{ $article->published_date ?? $article->created_at->format('Y年n月j日') }}</span>
                                            <span>{{ $article->author->name }}</span>
                                            <span>{{ __(':min分で読めます', ['min' => $article->reading_time]) }}</span>
                                            @if($this->canManageNews)
                                                <span>{{ __(':count回閲覧', ['count' => number_format($article->view_count)]) }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Actions (Managers Only) --}}
                                    @if($this->canManageNews)
                                        <div class="flex sm:flex-col gap-2" x-data="{ open: false }">
                                            <div class="relative">
                                                <button 
                                                    @click.prevent="open = !open"
                                                    class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                                    </svg>
                                                </button>

                                                {{-- Dropdown Menu --}}
                                                <div 
                                                    x-show="open"
                                                    @click.away="open = false"
                                                    x-transition
                                                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-10"
                                                >
                                                    <a 
                                                        href="{{ route('news.edit', $article) }}"
                                                        wire:navigate
                                                        class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                    >
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                        {{ __('編集') }}
                                                    </a>

                                                    <button 
                                                        wire:click.prevent="togglePinned({{ $article->id }})"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                    >
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                                        </svg>
                                                        {{ $article->is_pinned ? __('ピン解除') : __('ピン留め') }}
                                                    </button>

                                                    <hr class="my-1 border-gray-200 dark:border-gray-700">

                                                    <button 
                                                        wire:click.prevent="delete({{ $article->id }})"
                                                        wire:confirm="{{ __('この記事を削除してもよろしいですか？') }}"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30"
                                                    >
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                        {{ __('削除') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </article>
                @empty
                    {{-- Empty State --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            {{ __('ニュースがありません') }}
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">
                            @if($search || $category)
                                {{ __('検索条件に一致するニュースが見つかりませんでした。') }}
                            @else
                                {{ __('まだニュースが投稿されていません。') }}
                            @endif
                        </p>
                        @if($this->canManageNews)
                            <a 
                                href="{{ route('news.create') }}"
                                wire:navigate
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                {{ __('最初のニュースを作成') }}
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($this->news->hasPages())
                <div class="mt-6">
                    {{ $this->news->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
