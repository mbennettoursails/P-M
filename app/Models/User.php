<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, LogsActivity, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Configure activity logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "User '{$this->name}' was {$eventName}");
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if the user is a regular user.
     */
    public function isUser(): bool
    {
        return $this->hasRole('user');
    }

    /**
     * Scope a query to only include admin users.
     */
    public function scopeAdmins($query)
    {
        return $query->role('admin');
    }

    /**
     * Scope a query to only include regular users.
     */
    public function scopeUsers($query)
    {
        return $query->role('user');
    }

    /**
     * Get the user's primary role name.
     */
    public function getPrimaryRoleAttribute(): string
    {
        return $this->roles->first()?->name ?? 'user';
    }

    /**
     * Get the user's role display name.
     */
    public function getRoleDisplayNameAttribute(): string
    {
        $role = $this->primary_role;
        
        return match ($role) {
            'admin' => 'Admin',
            'user' => 'User',
            default => ucfirst($role),
        };
    }
}
