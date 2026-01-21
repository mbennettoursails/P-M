# Baseline Laravel - Admin System

## Overview

The Baseline Laravel admin system provides a modular, privacy-first administration panel built on industry-standard Spatie packages. The `admin` role has full control over the application including user management, settings, roles & permissions, audit logging, and system health monitoring.

## Architecture

### Role Structure

| Role | Description | Capabilities |
|------|-------------|--------------|
| `admin` | System administrator | Full access to all admin features |
| `user` | Regular user | Access to user-facing features only |

### Packages Used

| Package | Version | Purpose |
|---------|---------|---------|
| `spatie/laravel-permission` | Latest | Role & permission management |
| `spatie/laravel-settings` | Latest | Application settings (typed, cached) |
| `spatie/laravel-activitylog` | Latest | Audit logging for all changes |

---

## Installation

### 1. Install Packages

```bash
chmod +x install-packages.sh
./install-packages.sh
```

Or manually:

```bash
composer require spatie/laravel-permission
composer require spatie/laravel-settings
composer require spatie/laravel-activitylog

php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\LaravelSettings\LaravelSettingsServiceProvider"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-config"
```

### 2. Run Migrations

```bash
php artisan migrate
```

### 3. Seed Default Data

```bash
php artisan db:seed
```

This creates:
- `admin` and `user` roles with permissions
- Default admin user: `admin@example.com` / `Admin123!Change`
- Test user (dev only): `user@example.com` / `password`

⚠️ **IMPORTANT**: Change the default admin password immediately in production!

### 4. Clear Caches

```bash
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan permission:cache-reset
```

---

## Admin Panel Features

### Dashboard (`/admin/dashboard`)

- **User Metrics**: Total users, admins, regular users, new signups
- **Recent Activity**: Latest audit log entries
- **System Information**: Laravel version, PHP, environment, debug status
- **Quick Actions**: Links to all admin sections
- **Settings Summary**: Current app configuration at a glance

### User Management (`/admin/users`)

- List all users with search and filters
- View user details and permissions
- Promote/demote users (toggle admin role)
- Filter by role, date range
- Self-demotion prevention (admins cannot remove their own role)

### Settings (`/admin/settings`)

| Setting | Description |
|---------|-------------|
| **App Name** | Application display name |
| **App Logo** | Upload custom logo (JPG, PNG, SVG, max 2MB) |
| **Timezone** | Application timezone |
| **Maintenance Mode** | Restrict access to admins only |
| **Registration** | Enable/disable new user registration |
| **Default Role** | Role assigned to newly registered users |

### Roles & Permissions (`/admin/roles`)

- View all roles and their permission counts
- Create custom roles with granular permissions
- Edit existing role permissions
- Delete non-system roles (custom roles only)
- System roles (`admin`, `user`) are protected from deletion/rename

### Audit Logs (`/admin/audit-logs`)

- View all system activity chronologically
- Filter by event type, date range
- Search in descriptions
- Expand to view detailed change properties (JSON)
- Clear old logs with configurable retention period

### System Health (`/admin/system-health`)

- **Database**: Connection status and response time
- **Cache**: Read/write test and response time
- **Storage**: File system read/write test
- **Queue**: Status (if using database driver)
- **Disk Usage**: Visual bar with percentage
- **PHP Info**: Version, memory limit, extensions
- **Laravel Info**: Version, environment, debug mode
- **Cache Management**: Clear app/config/view/route caches

---

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Admin/
│   │       ├── AdminDashboardController.php
│   │       ├── AdminUsersController.php
│   │       ├── AuditLogController.php
│   │       ├── RolesController.php
│   │       ├── SettingsController.php
│   │       └── SystemHealthController.php
│   └── Middleware/
│       ├── IsAdmin.php
│       └── CheckRegistration.php
├── Models/
│   └── User.php (with HasRoles, LogsActivity traits)
└── Settings/
    └── GeneralSettings.php

database/
├── migrations/
│   └── 2025_01_20_000001_remove_role_column_from_users_table.php
├── seeders/
│   ├── AdminUserSeeder.php
│   ├── DatabaseSeeder.php
│   └── RolesAndPermissionsSeeder.php
└── settings/
    └── 2025_01_20_000001_create_general_settings.php

resources/views/
├── layouts/
│   └── admin.blade.php
└── admin/
    ├── dashboard.blade.php
    ├── users/
    │   ├── index.blade.php
    │   └── show.blade.php
    ├── settings/
    │   └── index.blade.php
    ├── roles/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   └── edit.blade.php
    ├── audit-logs/
    │   └── index.blade.php
    └── system-health/
        └── index.blade.php

routes/
└── web.php (admin routes with middleware)
```

---

## Routes Reference

| Route | Method | Controller | Description |
|-------|--------|------------|-------------|
| `/admin/dashboard` | GET | AdminDashboardController@index | Main dashboard |
| `/admin/users` | GET | AdminUsersController@index | User list |
| `/admin/users/{user}` | GET | AdminUsersController@show | User details |
| `/admin/users/{user}/toggle-admin` | POST | AdminUsersController@toggleAdmin | Promote/demote |
| `/admin/settings` | GET | SettingsController@index | Settings form |
| `/admin/settings` | PUT | SettingsController@update | Save settings |
| `/admin/roles` | GET | RolesController@index | Roles list |
| `/admin/roles/create` | GET | RolesController@create | Create role form |
| `/admin/roles` | POST | RolesController@store | Save new role |
| `/admin/roles/{role}/edit` | GET | RolesController@edit | Edit role form |
| `/admin/roles/{role}` | PUT | RolesController@update | Update role |
| `/admin/roles/{role}` | DELETE | RolesController@destroy | Delete role |
| `/admin/audit-logs` | GET | AuditLogController@index | Activity log |
| `/admin/audit-logs/clear` | POST | AuditLogController@clear | Clear old logs |
| `/admin/system-health` | GET | SystemHealthController@index | Health dashboard |
| `/admin/system-health/clear-*` | POST | SystemHealthController@clear* | Clear caches |

---

## Middleware

### `admin` Middleware

Protects routes requiring admin access. Redirects to login if unauthenticated, returns 403 if not admin.

```php
// routes/web.php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    // ... more admin routes
});
```

### `registration` Middleware

Blocks registration when disabled in settings. Apply to registration routes:

```php
Route::middleware('registration')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create']);
    Route::post('register', [RegisteredUserController::class, 'store']);
});
```

---

## Using Settings in Your Application

### Via Dependency Injection (Recommended)

```php
use App\Settings\GeneralSettings;

class WelcomeController extends Controller
{
    public function index(GeneralSettings $settings)
    {
        return view('welcome', [
            'appName' => $settings->app_name,
            'isMaintenanceMode' => $settings->maintenance_mode,
        ]);
    }
}
```

### Via Helper

```php
$settings = app(GeneralSettings::class);
$appName = $settings->app_name;
$registrationEnabled = $settings->registration_enabled;
```

### In Blade Templates

```blade
@inject('settings', 'App\Settings\GeneralSettings')

<title>{{ $settings->app_name }}</title>

@if($settings->maintenance_mode)
    <div class="alert">Site is in maintenance mode</div>
@endif
```

---

## Activity Logging

### Automatic Logging (Models)

Models with the `LogsActivity` trait automatically log create/update/delete events:

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email'])  // Only log these fields
            ->logOnlyDirty()              // Only log changed fields
            ->dontSubmitEmptyLogs();      // Skip if nothing changed
    }
}
```

### Manual Logging

```php
activity()
    ->causedBy(auth()->user())
    ->performedOn($user)
    ->withProperties(['role' => 'admin'])
    ->log('User promoted to admin');
```

### Scheduled Cleanup

Activity logs are automatically cleaned up via scheduled task (90 days retention). See `bootstrap/app.php`:

```php
$schedule->command('activitylog:clean --days=90')
         ->daily()
         ->at('03:00');
```

---

## Permissions Reference

| Permission | Description |
|------------|-------------|
| `view users` | View user list and details |
| `create users` | Create new users |
| `edit users` | Edit user information |
| `delete users` | Delete users |
| `view roles` | View roles and permissions |
| `create roles` | Create new roles |
| `edit roles` | Edit role permissions |
| `delete roles` | Delete custom roles |
| `assign roles` | Assign roles to users |
| `view settings` | View application settings |
| `edit settings` | Modify application settings |
| `view audit logs` | View activity logs |
| `clear audit logs` | Delete old activity logs |
| `view system health` | View system status |
| `clear cache` | Clear application caches |
| `access telescope` | Access Laravel Telescope |

---

## Security Considerations

1. **Change Default Credentials**: Immediately change `admin@example.com` password in production
2. **HTTPS Only**: Always use HTTPS in production
3. **Role Protection**: System roles (`admin`, `user`) cannot be renamed or deleted
4. **Self-Protection**: Admins cannot remove their own admin role
5. **Audit Trail**: All admin actions are logged with user, timestamp, and changes
6. **Input Validation**: All forms validate and sanitize input

---

## Extending the Admin System

### Adding New Permissions

1. Edit `database/seeders/RolesAndPermissionsSeeder.php`:
   ```php
   $permissions = [
       // ... existing permissions
       'manage orders',
       'view reports',
   ];
   ```

2. Re-run seeder:
   ```bash
   php artisan db:seed --class=RolesAndPermissionsSeeder
   ```

### Adding New Settings

1. Create settings migration in `database/settings/`:
   ```php
   $this->migrator->add('general.new_setting', 'default_value');
   ```

2. Add property to `app/Settings/GeneralSettings.php`:
   ```php
   public string $new_setting;
   ```

3. Run migration:
   ```bash
   php artisan migrate
   ```

### Adding New Admin Sections

1. Create controller: `app/Http/Controllers/Admin/NewController.php`
2. Add routes to `routes/web.php` under admin group
3. Create views in `resources/views/admin/new-section/`
4. Add navigation link in `resources/views/layouts/admin.blade.php`

---

## Troubleshooting

### Roles/Permissions Not Working

```bash
php artisan permission:cache-reset
php artisan cache:clear
```

### Settings Not Updating

```bash
php artisan cache:clear
```

### Activity Logs Not Recording

Ensure the model has:
1. `use LogsActivity;` trait
2. `getActivitylogOptions()` method defined

### 403 Forbidden After Login

Check that the user has the `admin` role:
```bash
php artisan tinker
>>> User::find(1)->roles->pluck('name')
```

Assign if missing:
```bash
>>> User::find(1)->assignRole('admin')
```

---

## Environment Variables

No additional environment variables required. All settings are managed through the admin panel and stored in the database.

---

## License

MIT License - See LICENSE file for details.
