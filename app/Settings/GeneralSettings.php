<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    /**
     * Application name displayed throughout the app.
     */
    public string $app_name;

    /**
     * Path to the application logo.
     */
    public ?string $app_logo;

    /**
     * Application timezone.
     */
    public string $timezone;

    /**
     * Whether the app is in maintenance mode.
     */
    public bool $maintenance_mode;

    /**
     * Whether new user registration is enabled.
     */
    public bool $registration_enabled;

    /**
     * Default role assigned to new users.
     */
    public string $default_user_role;

    /**
     * The settings group name.
     */
    public static function group(): string
    {
        return 'general';
    }

    /**
     * Get all settings as an array.
     */
    public function toArray(): array
    {
        return [
            'app_name' => $this->app_name,
            'app_logo' => $this->app_logo,
            'timezone' => $this->timezone,
            'maintenance_mode' => $this->maintenance_mode,
            'registration_enabled' => $this->registration_enabled,
            'default_user_role' => $this->default_user_role,
        ];
    }
}
