<?php

namespace App\Livewire\Events;

use App\Models\Event;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;

class EventForm extends Component
{
    use AuthorizesRequests, WithFileUploads;

    public ?Event $event = null;
    public bool $isEdit = false;

    // Basic Information
    public string $title = '';
    public string $title_en = '';
    public string $description = '';
    public string $description_en = '';
    public string $content = '';
    public ?array $content_json = null;

    // Date & Time
    public string $starts_at_date = '';
    public string $starts_at_time = '';
    public string $ends_at_date = '';
    public string $ends_at_time = '';
    public bool $is_all_day = false;

    // Location
    public string $location = '';
    public string $location_en = '';
    public string $address = '';
    public bool $is_online = false;
    public string $online_url = '';

    // Capacity & Registration
    public ?int $capacity = null;
    public bool $registration_required = true;
    public string $registration_opens_at = '';
    public string $registration_closes_at = '';
    public bool $waitlist_enabled = false;

    // Categorization
    public string $category = 'general';
    public string $color = 'primary';

    // Display
    public $featured_image;
    public ?string $existing_featured_image = null;
    public string $featured_image_alt = '';

    // Status
    public string $status = 'draft';
    public bool $is_featured = false;
    public bool $is_pinned = false;
    public array $visible_to_roles = [];

    // Cost
    public int $cost = 0;
    public string $cost_notes = '';

    public function mount(?Event $event = null): void
    {
        if ($event && $event->exists) {
            $this->authorize('update', $event);
            $this->isEdit = true;
            $this->event = $event;
            $this->fillFromEvent($event);
        } else {
            $this->authorize('create', Event::class);
        }
    }

    protected function fillFromEvent(Event $event): void
    {
        $this->title = $event->title;
        $this->title_en = $event->title_en ?? '';
        $this->description = $event->description ?? '';
        $this->description_en = $event->description_en ?? '';
        $this->content = $event->content ?? '';
        $this->content_json = $event->content_json;

        $this->starts_at_date = $event->starts_at->format('Y-m-d');
        $this->starts_at_time = $event->starts_at->format('H:i');
        $this->ends_at_date = $event->ends_at?->format('Y-m-d') ?? '';
        $this->ends_at_time = $event->ends_at?->format('H:i') ?? '';
        $this->is_all_day = $event->is_all_day;

        $this->location = $event->location ?? '';
        $this->location_en = $event->location_en ?? '';
        $this->address = $event->address ?? '';
        $this->is_online = $event->is_online;
        $this->online_url = $event->online_url ?? '';

        $this->capacity = $event->capacity;
        $this->registration_required = $event->registration_required;
        $this->registration_opens_at = $event->registration_opens_at?->format('Y-m-d\TH:i') ?? '';
        $this->registration_closes_at = $event->registration_closes_at?->format('Y-m-d\TH:i') ?? '';
        $this->waitlist_enabled = $event->waitlist_enabled;

        $this->category = $event->category;
        $this->color = $event->color;

        $this->existing_featured_image = $event->featured_image;
        $this->featured_image_alt = $event->featured_image_alt ?? '';

        $this->status = $event->status;
        $this->is_featured = $event->is_featured;
        $this->is_pinned = $event->is_pinned;
        $this->visible_to_roles = $event->visible_to_roles ?? [];

        $this->cost = $event->cost ?? 0;
        $this->cost_notes = $event->cost_notes ?? '';
    }

    #[Computed]
    public function categories(): array
    {
        return Event::CATEGORIES;
    }

    #[Computed]
    public function colors(): array
    {
        return Event::COLORS;
    }

    #[Computed]
    public function statuses(): array
    {
        return Event::STATUSES;
    }

    #[Computed]
    public function availableRoles(): array
    {
        return [
            'volunteer' => 'ボランティア',
            'reijikai' => '委員会',
            'shokuin' => '職員',
        ];
    }

    public function updatedFeaturedImage(): void
    {
        $this->validate([
            'featured_image' => 'image|max:5120', // 5MB max
        ]);
    }

    public function removeFeaturedImage(): void
    {
        $this->featured_image = null;
        $this->existing_featured_image = null;
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'description_en' => 'nullable|string|max:1000',
            'content' => 'nullable|string',
            'content_json' => 'nullable|array',

            'starts_at_date' => 'required|date',
            'starts_at_time' => 'required_unless:is_all_day,true',
            'ends_at_date' => 'nullable|date|after_or_equal:starts_at_date',
            'ends_at_time' => 'nullable',
            'is_all_day' => 'boolean',

            'location' => 'nullable|string|max:255',
            'location_en' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'is_online' => 'boolean',
            'online_url' => 'nullable|url|max:500',

            'capacity' => 'nullable|integer|min:1|max:10000',
            'registration_required' => 'boolean',
            'registration_opens_at' => 'nullable|date',
            'registration_closes_at' => 'nullable|date|after_or_equal:registration_opens_at',
            'waitlist_enabled' => 'boolean',

            'category' => 'required|in:' . implode(',', array_keys(Event::CATEGORIES)),
            'color' => 'required|in:' . implode(',', array_keys(Event::COLORS)),

            'featured_image' => 'nullable|image|max:5120',
            'featured_image_alt' => 'nullable|string|max:255',

            'status' => 'required|in:' . implode(',', array_keys(Event::STATUSES)),
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
            'visible_to_roles' => 'nullable|array',

            'cost' => 'integer|min:0|max:1000000',
            'cost_notes' => 'nullable|string|max:255',
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => __('events.validation.title_required'),
            'starts_at_date.required' => __('events.validation.date_required'),
            'starts_at_time.required_unless' => __('events.validation.time_required'),
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Build datetime values
        $startsAt = $this->buildDateTime($this->starts_at_date, $this->starts_at_time, $this->is_all_day);
        $endsAt = $this->ends_at_date ? $this->buildDateTime($this->ends_at_date, $this->ends_at_time, $this->is_all_day) : null;

        // Handle featured image upload
        $featuredImagePath = $this->existing_featured_image;
        if ($this->featured_image) {
            $featuredImagePath = $this->featured_image->store('events', 'public');
        }

        $data = [
            'title' => $this->title,
            'title_en' => $this->title_en ?: null,
            'description' => $this->description ?: null,
            'description_en' => $this->description_en ?: null,
            'content' => $this->content ?: null,
            'content_json' => $this->content_json,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'is_all_day' => $this->is_all_day,
            'location' => $this->location ?: null,
            'location_en' => $this->location_en ?: null,
            'address' => $this->address ?: null,
            'is_online' => $this->is_online,
            'online_url' => $this->online_url ?: null,
            'capacity' => $this->capacity,
            'registration_required' => $this->registration_required,
            'registration_opens_at' => $this->registration_opens_at ? \Carbon\Carbon::parse($this->registration_opens_at) : null,
            'registration_closes_at' => $this->registration_closes_at ? \Carbon\Carbon::parse($this->registration_closes_at) : null,
            'waitlist_enabled' => $this->waitlist_enabled,
            'category' => $this->category,
            'color' => $this->color,
            'featured_image' => $featuredImagePath,
            'featured_image_alt' => $this->featured_image_alt ?: null,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'is_pinned' => $this->is_pinned,
            'visible_to_roles' => !empty($this->visible_to_roles) ? $this->visible_to_roles : null,
            'cost' => $this->cost,
            'cost_notes' => $this->cost_notes ?: null,
        ];

        if ($this->isEdit) {
            $this->event->update($data);
            session()->flash('success', __('events.messages.updated'));
        } else {
            $data['organizer_id'] = auth()->id();
            $data['created_by'] = auth()->id();
            $event = Event::create($data);
            session()->flash('success', __('events.messages.created'));
            $this->redirect(route('events.show', $event->uuid), navigate: true);
            return;
        }

        $this->redirect(route('events.show', $this->event->uuid), navigate: true);
    }

    protected function buildDateTime(string $date, ?string $time, bool $isAllDay): \Carbon\Carbon
    {
        if ($isAllDay) {
            return \Carbon\Carbon::parse($date)->startOfDay();
        }
        
        return \Carbon\Carbon::parse($date . ' ' . ($time ?? '00:00'));
    }

    public function render()
    {
        $title = $this->isEdit ? __('events.edit.title') : __('events.create.title');
        
        return view('livewire.events.event-form')
            ->layout('layouts.app', ['title' => $title]);
    }
}
