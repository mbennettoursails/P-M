<?php

namespace App\Livewire\Events;

use App\Models\Event;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Illuminate\Support\Carbon;

class EventsIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $filter = 'upcoming'; // upcoming, past, all

    #[Url]
    public string $category = '';

    #[Url]
    public string $viewMode = 'list'; // list, calendar

    // Calendar-specific state (not in URL for list view)
    public int $calendarMonth = 0;
    public int $calendarYear = 0;

    protected $queryString = [
        'filter' => ['except' => 'upcoming'],
        'category' => ['except' => ''],
        'viewMode' => ['except' => 'list'],
    ];

    public function mount(): void
    {
        // Initialize calendar to current month
        $this->calendarMonth = now()->month;
        $this->calendarYear = now()->year;
    }

    #[Computed]
    public function events()
    {
        $query = Event::query()
            ->published()
            ->visibleTo(auth()->user())
            ->with(['organizer', 'confirmedAttendees']);

        // Apply category filter first
        if ($this->category) {
            $query->category($this->category);
        }

        // Apply time-based filter
        if ($this->viewMode === 'calendar') {
            // Calendar view: show events for the selected month
            $query->inMonth($this->calendarMonth, $this->calendarYear)
                  ->orderBy('starts_at', 'asc');
        } else {
            // List view: apply upcoming/past/all filter
            match ($this->filter) {
                'upcoming' => $query->upcoming()->orderBy('is_pinned', 'desc')->orderBy('starts_at', 'asc'),
                'past' => $query->past()->orderBy('starts_at', 'desc'),
                default => $query->orderBy('is_pinned', 'desc')->orderBy('starts_at', 'desc'), // all
            };
        }

        return $query->paginate(12);
    }

    #[Computed]
    public function categories(): array
    {
        return Event::CATEGORIES;
    }

    #[Computed]
    public function currentMonthLabel(): string
    {
        return Carbon::create($this->calendarYear, $this->calendarMonth, 1)->format('Y年n月');
    }

    #[Computed]
    public function calendarDays(): array
    {
        $startOfMonth = Carbon::create($this->calendarYear, $this->calendarMonth, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        
        $days = [];
        $current = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        
        // Build query for events in this month's view range
        $viewStart = $current->copy();
        $viewEnd = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);
        
        // Get events for the calendar view range
        $monthEvents = Event::query()
            ->published()
            ->visibleTo(auth()->user())
            ->whereBetween('starts_at', [$viewStart, $viewEnd])
            ->orderBy('starts_at', 'asc')
            ->get()
            ->groupBy(fn ($event) => $event->starts_at->format('Y-m-d'));

        while ($current->lte($viewEnd)) {
            $dateKey = $current->format('Y-m-d');
            $days[] = [
                'date' => $current->copy(),
                'day' => $current->day,
                'isCurrentMonth' => $current->month === $this->calendarMonth,
                'isToday' => $current->isToday(),
                'events' => $monthEvents->get($dateKey, collect()),
            ];
            $current->addDay();
        }

        return $days;
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function setCategory(string $category): void
    {
        // Toggle category - clicking same category clears filter
        $this->category = $this->category === $category ? '' : $category;
        $this->resetPage();
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
        
        // When switching to calendar, ensure we're on current month
        if ($mode === 'calendar') {
            $this->calendarMonth = now()->month;
            $this->calendarYear = now()->year;
        }
        
        $this->resetPage();
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->subMonth();
        $this->calendarMonth = $date->month;
        $this->calendarYear = $date->year;
        $this->resetPage();
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->addMonth();
        $this->calendarMonth = $date->month;
        $this->calendarYear = $date->year;
        $this->resetPage();
    }

    public function goToToday(): void
    {
        $this->calendarMonth = now()->month;
        $this->calendarYear = now()->year;
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.events.events-index')
            ->layout('layouts.app', ['title' => __('events.title')]);
    }
}
