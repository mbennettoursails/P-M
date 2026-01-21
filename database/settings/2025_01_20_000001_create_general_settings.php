<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Create the general application settings.
     */
    public function up(): void
    {
        $this->migrator->add('general.app_name', config('app.name', 'Laravel'));
        $this->migrator->add('general.app_logo', null);
        $this->migrator->add('general.timezone', config('app.timezone', 'UTC'));
        $this->migrator->add('general.maintenance_mode', false);
        $this->migrator->add('general.registration_enabled', true);
        $this->migrator->add('general.default_user_role', 'user');
    }

    /**
     * Reverse the settings migration.
     */
    public function down(): void
    {
        $this->migrator->delete('general.app_name');
        $this->migrator->delete('general.app_logo');
        $this->migrator->delete('general.timezone');
        $this->migrator->delete('general.maintenance_mode');
        $this->migrator->delete('general.registration_enabled');
        $this->migrator->delete('general.default_user_role');
    }
};
