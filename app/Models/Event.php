<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'title',
        'title_en',
        'slug',
        'description',
        'description_en',
        'content',
        'content_json',
        'starts_at',
        'ends_at',
        'is_all_day',
        'location',
        'location_en',
        'address',
        'latitude',
        'longitude',
        'is_online',
        'online_url',
        'capacity',
        'registration_required',
        'registration_opens_at',
        'registration_closes_at',
        'waitlist_enabled',
        'category',
        'featured_image',
        'featured_image_alt',
        'color',
        'status',
        'visible_to_roles',
        'is_featured',
        'is_pinned',
        'organizer_id',
        'created_by',
        'view_count',
        'cost',
        'cost_notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'registration_opens_at' => 'datetime',
        'registration_closes_at' => 'datetime',
        'is_all_day' => 'boolean',
        'is_online' => 'boolean',
        'registration_required' => 'boolean',
        'waitlist_enabled' => 'boolean',
        'is_featured' => 'boolean',
        'is_pinned' => 'boolean',
        'visible_to_roles' => 'array',
        'content_json' => 'array',
        'capacity' => 'integer',
        'view_count' => 'integer',
        'cost' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Category options with Japanese labels
     */
    public const CATEGORIES = [
        'general' => '一般',
        'workshop' => 'ワークショップ',
        'meeting' => '会議',
        'social' => '交流会',
        'volunteer' => 'ボランティア',
        'cooking' => '料理教室',
        'lecture' => '講座',
        'other' => 'その他',
    ];

    /**
     * Status options with Japanese labels
     */
    public const STATUSES = [
        'draft' => '下書き',
        'published' => '公開中',
        'cancelled' => '中止',
        'completed' => '終了',
    ];

    /**
     * Color options for event theming (using Tailwind color names)
     * Note: In views, we primarily use 'primary' for consistency with the PWA design system
     */
    public const COLORS = [
        'primary' => 'プライマリー（緑）',
        'blue' => 'ブルー',
        'cyan' => 'シアン',
        'orange' => 'オレンジ',
        'pink' => 'ピンク',
        'purple' => '紫',
        'red' => '赤',
        'amber' => '黄色',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->uuid)) {
                $event->uuid = (string) Str::uuid();
            }
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title) . '-' . Str::random(6);
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ─────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────

    /**
     * Event organizer
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * User who created the event
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Registered attendees
     */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user')
            ->withPivot(['status', 'notes', 'admin_notes', 'guests', 'registered_at', 'cancelled_at', 'attended_at'])
            ->withTimestamps();
    }

    /**
     * Only confirmed registrations
     */
    public function confirmedAttendees(): BelongsToMany
    {
        return $this->attendees()->wherePivot('status', 'registered');
    }

    /**
     * Waitlisted users
     */
    public function waitlist(): BelongsToMany
    {
        return $this->attendees()->wherePivot('status', 'waitlisted');
    }

    // ─────────────────────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────────────────────

    /**
     * Only published events
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Only draft events
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * Cancelled events
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Upcoming events (starts in the future)
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now());
    }

    /**
     * Past events
     */
    public function scopePast(Builder $query): Builder
    {
        return $query->where('starts_at', '<', now());
    }

    /**
     * Events this month
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('starts_at', now()->month)
            ->whereYear('starts_at', now()->year);
    }

    /**
     * Events in a specific month/year
     */
    public function scopeInMonth(Builder $query, int $month, int $year): Builder
    {
        return $query->whereMonth('starts_at', $month)
            ->whereYear('starts_at', $year);
    }

    /**
     * Events on a specific date
     */
    public function scopeOnDate(Builder $query, $date): Builder
    {
        $date = Carbon::parse($date);
        return $query->whereDate('starts_at', $date);
    }

    /**
     * Filter by category
     */
    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Featured events
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Pinned events first
     */
    public function scopePinnedFirst(Builder $query): Builder
    {
        return $query->orderByDesc('is_pinned');
    }

    /**
     * Events with available spots
     */
    public function scopeHasAvailability(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('capacity')
                ->orWhereRaw('capacity > (SELECT COUNT(*) FROM event_user WHERE event_user.event_id = events.id AND event_user.status = ?)' , ['registered']);
        });
    }

    /**
     * Events visible to a specific user based on their roles
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $userRoles = $user->roles->pluck('name')->toArray();
        
        return $query->where(function ($q) use ($userRoles) {
            $q->whereNull('visible_to_roles');
            
            foreach ($userRoles as $role) {
                $q->orWhereJsonContains('visible_to_roles', $role);
            }
        });
    }

    /**
     * Events where registration is open
     */
    public function scopeRegistrationOpen(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where(function ($inner) {
                $inner->whereNull('registration_opens_at')
                    ->orWhere('registration_opens_at', '<=', now());
            })->where(function ($inner) {
                $inner->whereNull('registration_closes_at')
                    ->orWhere('registration_closes_at', '>=', now());
            });
        });
    }

    // ─────────────────────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────────────────────

    /**
     * Get the localized title based on app locale
     */
    public function getDisplayTitleAttribute(): string
    {
        if (app()->getLocale() === 'en' && $this->title_en) {
            return $this->title_en;
        }
        return $this->title;
    }

    /**
     * Get the localized description based on app locale
     */
    public function getDisplayDescriptionAttribute(): string
    {
        if (app()->getLocale() === 'en' && $this->description_en) {
            return $this->description_en;
        }
        return $this->description ?? '';
    }

    /**
     * Get the localized location based on app locale
     */
    public function getDisplayLocationAttribute(): string
    {
        if (app()->getLocale() === 'en' && $this->location_en) {
            return $this->location_en;
        }
        return $this->location ?? '';
    }

    /**
     * Get the category label in Japanese
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Get the status label in Japanese
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get formatted date in Japanese format
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->starts_at->format('Y年n月j日');
    }

    /**
     * Get formatted time range
     */
    public function getFormattedTimeAttribute(): string
    {
        if ($this->is_all_day) {
            return '終日';
        }
        
        $time = $this->starts_at->format('H:i');
        if ($this->ends_at) {
            $time .= '〜' . $this->ends_at->format('H:i');
        }
        return $time;
    }

    /**
     * Get full formatted datetime
     */
    public function getFormattedDateTimeAttribute(): string
    {
        return $this->formatted_date . ' ' . $this->formatted_time;
    }

    /**
     * Get day of week in Japanese
     */
    public function getDayOfWeekAttribute(): string
    {
        $days = ['日', '月', '火', '水', '木', '金', '土'];
        return $days[$this->starts_at->dayOfWeek];
    }

    /**
     * Get count of confirmed registrations
     */
    public function getRegistrationCountAttribute(): int
    {
        return $this->confirmedAttendees()->count();
    }

    /**
     * Get total guests (including additional guests)
     */
    public function getTotalGuestsAttribute(): int
    {
        return $this->confirmedAttendees()->sum('guests') + $this->registration_count;
    }

    /**
     * Get remaining spots
     */
    public function getRemainingAttribute(): ?int
    {
        if ($this->capacity === null) {
            return null;
        }
        return max(0, $this->capacity - $this->total_guests);
    }

    /**
     * Check if event is full
     */
    public function getIsFullAttribute(): bool
    {
        if ($this->capacity === null) {
            return false;
        }
        return $this->remaining <= 0;
    }

    /**
     * Check if event is upcoming
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->starts_at->isFuture();
    }

    /**
     * Check if event is past
     */
    public function getIsPastAttribute(): bool
    {
        return $this->starts_at->isPast();
    }

    /**
     * Check if event is happening today
     */
    public function getIsTodayAttribute(): bool
    {
        return $this->starts_at->isToday();
    }

    /**
     * Check if registration is currently open
     */
    public function getIsRegistrationOpenAttribute(): bool
    {
        if (!$this->registration_required) {
            return false;
        }
        
        if ($this->status !== 'published') {
            return false;
        }
        
        $now = now();
        
        if ($this->registration_opens_at && $now->lt($this->registration_opens_at)) {
            return false;
        }
        
        if ($this->registration_closes_at && $now->gt($this->registration_closes_at)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get cost display string
     */
    public function getCostDisplayAttribute(): string
    {
        if ($this->cost === 0) {
            return '無料';
        }
        
        $display = number_format($this->cost) . '円';
        if ($this->cost_notes) {
            $display .= ' (' . $this->cost_notes . ')';
        }
        return $display;
    }

    /**
     * Get featured image URL
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }
        
        return asset('storage/' . $this->featured_image);
    }

    // ─────────────────────────────────────────────────────────────
    // HELPER METHODS
    // ─────────────────────────────────────────────────────────────

    /**
     * Check if a user is registered for this event
     */
    public function isUserRegistered(User $user): bool
    {
        return $this->attendees()
            ->where('user_id', $user->id)
            ->wherePivot('status', 'registered')
            ->exists();
    }

    /**
     * Check if a user is on the waitlist
     */
    public function isUserWaitlisted(User $user): bool
    {
        return $this->attendees()
            ->where('user_id', $user->id)
            ->wherePivot('status', 'waitlisted')
            ->exists();
    }

    /**
     * Get a user's registration status
     */
    public function getUserRegistrationStatus(User $user): ?string
    {
        $registration = $this->attendees()
            ->where('user_id', $user->id)
            ->first();
        
        return $registration?->pivot->status;
    }

    /**
     * Get a user's registration record
     */
    public function getUserRegistration(User $user)
    {
        return $this->attendees()->where('user_id', $user->id)->first();
    }

    /**
     * Register a user for this event
     */
    public function register(User $user, array $data = []): bool
    {
        if (!$this->is_registration_open) {
            return false;
        }

        if ($this->isUserRegistered($user)) {
            return false;
        }

        $status = 'registered';
        
        // Check capacity and add to waitlist if full
        if ($this->is_full && $this->waitlist_enabled) {
            $status = 'waitlisted';
        } elseif ($this->is_full) {
            return false;
        }

        $this->attendees()->attach($user->id, [
            'status' => $status,
            'notes' => $data['notes'] ?? null,
            'guests' => $data['guests'] ?? 0,
            'registered_at' => now(),
        ]);

        return true;
    }

    /**
     * Unregister a user from this event
     */
    public function unregister(User $user): bool
    {
        $registration = $this->getUserRegistration($user);
        
        if (!$registration) {
            return false;
        }

        $this->attendees()->updateExistingPivot($user->id, [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Promote first waitlisted user if available
        if ($this->waitlist_enabled) {
            $this->promoteFromWaitlist();
        }

        return true;
    }

    /**
     * Promote the first user from waitlist to registered
     */
    protected function promoteFromWaitlist(): void
    {
        $waitlisted = $this->waitlist()
            ->orderBy('event_user.created_at', 'asc')
            ->first();

        if ($waitlisted) {
            $this->attendees()->updateExistingPivot($waitlisted->id, [
                'status' => 'registered',
            ]);
        }
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }
}
