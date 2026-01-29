<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-header shadow-top-nav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 safe-x">
            <div class="py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 font-display">{{ __('events.title') }}</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 text-japanese">{{ __('events.subtitle') }}</p>
                    </div>
                    
                    @can('create', App\Models\Event::class)
                        <a href="{{ route('events.create') }}" 
                           wire:navigate
                           class="btn btn-primary tap-transparent">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('events.create.button') }}
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 safe-x">
        {{-- Filters & View Mode Toggle --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            {{-- Time Filter Tabs --}}
            <div class="flex bg-gray-100 dark:bg-gray-800 rounded-button p-1">
                <button wire:click="setFilter('upcoming')"
                        class="px-4 py-2 min-h-[40px] text-sm font-medium rounded-button transition-colors tap-transparent {{ $filter === 'upcoming' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-card' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    {{ __('events.filter.upcoming') }}
                </button>
                <button wire:click="setFilter('past')"
                        class="px-4 py-2 min-h-[40px] text-sm font-medium rounded-button transition-colors tap-transparent {{ $filter === 'past' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-card' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    {{ __('events.filter.past') }}
                </button>
                <button wire:click="setFilter('all')"
                        class="px-4 py-2 min-h-[40px] text-sm font-medium rounded-button transition-colors tap-transparent {{ $filter === 'all' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-card' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    {{ __('events.filter.all') }}
                </button>
            </div>

            {{-- View Mode Toggle --}}
            <div class="flex items-center gap-2">
                <button wire:click="setViewMode('list')"
                        class="touch-target flex items-center justify-center rounded-button transition-colors tap-transparent {{ $viewMode === 'list' ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </button>
                <button wire:click="setViewMode('calendar')"
                        class="touch-target flex items-center justify-center rounded-button transition-colors tap-transparent {{ $viewMode === 'calendar' ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Category Filter --}}
        <div class="flex flex-wrap gap-2 mb-6 scroll-smooth-touch overflow-x-auto scrollbar-hide pb-2 -mb-2">
            @foreach($this->categories as $key => $label)
                <button wire:click="setCategory('{{ $key }}')"
                        class="px-3 py-1.5 min-h-[36px] text-sm rounded-full-safe transition-colors tap-transparent whitespace-nowrap {{ $category === $key ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        @if($viewMode === 'calendar')
            {{-- Calendar View --}}
            <div class="card overflow-hidden">
                {{-- Calendar Header --}}
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <button wire:click="previousMonth" class="touch-target flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 rounded-button transition-colors tap-transparent">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $this->currentMonthLabel }}</h2>
                        <button wire:click="goToToday" class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-button transition-colors tap-transparent">
                            {{ __('events.calendar.today') }}
                        </button>
                    </div>
                    <button wire:click="nextMonth" class="touch-target flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 rounded-button transition-colors tap-transparent">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                {{-- Calendar Grid --}}
                <div class="grid grid-cols-7">
                    {{-- Day Headers --}}
                    @foreach(['日', '月', '火', '水', '木', '金', '土'] as $index => $dayName)
                        <div class="px-2 py-3 text-center text-xs font-medium {{ $index === 0 ? 'text-red-500' : ($index === 6 ? 'text-blue-500' : 'text-gray-500 dark:text-gray-400') }} border-b border-gray-200 dark:border-gray-700">
                            {{ $dayName }}
                        </div>
                    @endforeach

                    {{-- Calendar Days --}}
                    @foreach($this->calendarDays as $day)
                        <div class="min-h-[80px] sm:min-h-[100px] p-1 border-b border-r border-gray-100 dark:border-gray-700 {{ !$day['isCurrentMonth'] ? 'bg-gray-50 dark:bg-gray-800/50' : '' }}">
                            <div class="text-right mb-1">
                                <span class="inline-flex items-center justify-center w-7 h-7 text-sm rounded-full 
                                    {{ $day['isToday'] ? 'bg-primary-600 text-white font-bold' : '' }}
                                    {{ !$day['isCurrentMonth'] ? 'text-gray-400 dark:text-gray-600' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ $day['day'] }}
                                </span>
                            </div>
                            @foreach($day['events']->take(2) as $event)
                                <a href="{{ route('events.show', $event->uuid) }}" 
                                   wire:navigate
                                   class="block text-xs px-1.5 py-0.5 mb-0.5 rounded truncate bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 hover:bg-primary-200 dark:hover:bg-primary-900/50 transition-colors tap-transparent">
                                    {{ $event->display_title }}
                                </a>
                            @endforeach
                            @if($day['events']->count() > 2)
                                <span class="text-xs text-gray-500 dark:text-gray-400 px-1">+{{ $day['events']->count() - 2 }}件</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            {{-- List View --}}
            @if($this->events->isEmpty())
                <div class="card p-12 text-center">
                    <div class="empty-state">
                        <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <h3 class="empty-state-title">{{ __('events.empty.title') }}</h3>
                        <p class="empty-state-description text-japanese">{{ __('events.empty.description') }}</p>
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    @php $currentMonth = null; @endphp
                    @foreach($this->events as $event)
                        @php 
                            $eventMonth = $event->starts_at->format('Y年n月');
                        @endphp
                        
                        @if($currentMonth !== $eventMonth)
                            @php $currentMonth = $eventMonth; @endphp
                            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mt-6 mb-3 first:mt-0 font-display">{{ $eventMonth }}</h2>
                        @endif

                        <a href="{{ route('events.show', $event->uuid) }}" 
                           wire:navigate
                           class="block card card-hover tap-transparent">
                            <div class="p-4 flex items-start gap-4">
                                {{-- Date Badge --}}
                                <div class="w-14 h-14 bg-primary-100 dark:bg-primary-900/30 rounded-card flex flex-col items-center justify-center flex-shrink-0">
                                    <span class="text-xs text-primary-600 dark:text-primary-400 font-medium">{{ $event->starts_at->format('n月') }}</span>
                                    <span class="text-xl font-bold text-primary-700 dark:text-primary-300">{{ $event->starts_at->format('j') }}</span>
                                </div>
                                
                                {{-- Event Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        @if($event->is_pinned)
                                            <span class="badge badge-warning">
                                                {{ __('events.badges.pinned') }}
                                            </span>
                                        @endif
                                        <span class="badge badge-primary">
                                            {{ $event->category_label }}
                                        </span>
                                        @if($event->status === 'cancelled')
                                            <span class="badge badge-gray line-through">
                                                {{ __('events.status.cancelled') }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1 truncate text-japanese {{ $event->status === 'cancelled' ? 'line-through text-gray-500 dark:text-gray-400' : '' }}">
                                        {{ $event->display_title }}
                                    </h3>
                                    
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 text-japanese">
                                        @if($event->is_online)
                                            <span class="inline-flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                                {{ __('events.online') }}
                                            </span>
                                        @else
                                            {{ $event->display_location }}
                                        @endif
                                        • {{ $event->formatted_time }}
                                    </p>
                                    
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            👥 {{ $event->registration_count }}/{{ $event->capacity ?? '∞' }}{{ __('events.participants') }}
                                        </span>
                                        
                                        @if($event->is_registration_open && !$event->is_full)
                                            <span class="badge badge-success">
                                                {{ __('events.status.open') }}
                                            </span>
                                        @elseif($event->is_full)
                                            <span class="badge badge-warning">
                                                {{ $event->waitlist_enabled ? __('events.status.waitlist') : __('events.status.full') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Arrow --}}
                                <div class="flex-shrink-0 self-center">
                                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $this->events->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
