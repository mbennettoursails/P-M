# Baseline Laravel Project

A modern, production-ready Laravel baseline project template for building scalable web applications. This project comes pre-configured with authentication, admin features, modern tooling, and best practices.

## 🚀 Features

- **Laravel 12.x** - Latest version of the Laravel framework
- **Livewire/Volt** - Modern reactive component framework for building interactive UI
- **Tailwind CSS** - Utility-first CSS framework for rapid UI development
- **Authentication** - Pre-configured user authentication with Breeze
- **Admin Panel** - Built-in admin dashboard with user management
- **Database** - PostgreSQL support with migrations and seeders
- **Testing** - PHPUnit configured with feature and unit tests
- **Development Tools**:
  - Laravel Telescope for debugging and monitoring
  - Laravel Pail for log viewing
  - Laravel Pint for code formatting
  - Vite for fast asset bundling
- **Queues** - Database-backed job queue for background processing
- **Sessions & Cache** - Database-backed sessions and caching
- **Security Headers** - Custom security headers middleware pre-configured

## 📋 Tech Stack

| Component | Technology | Version |
|-----------|-----------|---------|
| Framework | Laravel | 12.x |
| PHP | PHP | 8.2+ |
| Database | PostgreSQL | 12+ |
| Frontend Framework | Livewire/Volt | 1.10+ |
| CSS Framework | Tailwind CSS | 3.1+ |
| Build Tool | Vite | 7.0+ |
| Testing | PHPUnit | 11.5+ |
| Code Quality | Laravel Pint | 1.24+ |

## ⚙️ Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & npm (v18+)
- PostgreSQL 12 or higher
- Git

## 🔧 Installation & Setup

### 1. Clone the Repository

```bash
git clone <repository-url>
cd Baseline-Laravel
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Update your `.env` file with your database credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 4. Database Setup

```bash
# Run migrations
php artisan migrate

# Seed the database with admin user
php artisan db:seed
```

This will create:
- Database tables for users, cache, jobs, and sessions
- An admin user (update credentials in `AdminUserSeeder.php`)

### 5. Build Assets

```bash
# Development build
npm run dev

# Production build
npm run build
```

### 6. Start Development Server

```bash
# Run all development services (server, queue, logs, vite)
npm run dev

# Or run individual services:
php artisan serve          # Start Laravel server
php artisan queue:listen   # Start queue worker
php artisan pail           # View logs in real-time
npm run dev               # Start Vite dev server
```

The application will be available at `http://localhost:8000`

## 📁 Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Application controllers
│   │   ├── Middleware/       # HTTP middleware
│   │   └── Requests/         # Form request validation
│   ├── Livewire/             # Livewire components
│   ├── Models/               # Eloquent models
│   ├── Providers/            # Service providers
│   └── View/
│       └── Components/       # View components
├── config/                   # Configuration files
├── database/
│   ├── factories/            # Model factories for testing
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── public/                   # Public assets
│   └── build/                # Compiled assets (git ignored)
├── resources/
│   ├── css/                  # CSS files
│   ├── js/                   # JavaScript files
│   └── views/                # Blade templates
├── routes/                   # Route definitions
├── storage/                  # Application storage
├── tests/                    # Test suites
└── vendor/                   # Composer packages
```

## 🔐 Authentication & Authorization

The project includes:

- **User Authentication** - Login/register functionality
- **Admin Middleware** - Protect admin routes with `IsAdmin` middleware
- **Profile Management** - User profile edit and password reset
- **Email Verification** - Email verification flow included

### Protected Routes Example

```php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
    Route::get('/admin/users', [AdminUsersController::class, 'index']);
});
```

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test tests/Feature

# Run with coverage
php artisan test --coverage
```

Tests are configured in `phpunit.xml` with:
- Unit tests for logic
- Feature tests for HTTP requests
- In-memory SQLite database for testing

## 🎨 Frontend Development

### Livewire/Volt Components

Create interactive components with Volt:

```php
// app/Livewire/Counter.php
<?php

namespace App\Livewire;

use Livewire\Volt\Component;

new class extends Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function decrement()
    {
        $this->count--;
    }
};
```

### Blade Templates

Templates use Blade with Tailwind CSS:

```blade
<div class="container mx-auto">
    <h1 class="text-2xl font-bold mb-4">Welcome</h1>
    <livewire:counter />
</div>
```

### Asset Bundling

Assets are handled by Vite:

```js
// resources/js/app.js
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
```

## 🛠️ Development Tools

### Laravel Telescope

Access the debug panel at `/telescope` (only in development):

```
http://localhost:8000/telescope
```

Monitors:
- Requests and responses
- Database queries
- Cache operations
- Jobs and exceptions

### Laravel Pail

View logs in real-time:

```bash
php artisan pail
```

### Code Formatting

Format code with Laravel Pint:

```bash
./vendor/bin/pint
```

## 🗄️ Database

### Migrations

Create a new migration:

```bash
php artisan make:migration create_posts_table
```

Run migrations:

```bash
# Run all pending migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Rollback all
php artisan migrate:reset
```

### Seeders

Create a seeder:

```bash
php artisan make:seeder PostSeeder
```

Run seeders:

```bash
php artisan db:seed
php artisan db:seed --class=PostSeeder
```

## 📧 Queue Jobs

Create a job:

```bash
php artisan make:job ProcessEmail
```

Dispatch a job:

```php
ProcessEmail::dispatch($email);
```

Listen for jobs:

```bash
php artisan queue:listen --tries=1
```

## 🚀 Deployment

### Production Build

```bash
# Build assets for production
npm run build

# Set environment to production
APP_ENV=production
APP_DEBUG=false

# Run migrations (if needed)
php artisan migrate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
```

### Environment Variables

Key production variables:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=pgsql
DB_HOST=your_db_host
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password

CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

## 📚 Documentation

- [Laravel Documentation](https://laravel.com/docs)
- [Livewire Documentation](https://livewire.laravel.com)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Vite Documentation](https://vitejs.dev)

## 🐛 Troubleshooting

### Composer Conflicts
```bash
composer update
```

### Node Modules Issues
```bash
rm -rf node_modules package-lock.json
npm install
```

### Database Connection
Verify PostgreSQL is running and credentials in `.env` are correct.

### Vite Assets Not Loading
```bash
npm run dev
# In another terminal:
php artisan serve
```

## 📝 Code Standards

This project follows:
- PSR-12 PHP coding standards (enforced by Laravel Pint)
- Laravel best practices
- DRY (Don't Repeat Yourself) principles

## 📄 License

MIT License - see LICENSE file for details.

## 🤝 Contributing

When using this as a baseline:

1. Update project-specific configuration in `config/app.php`
2. Customize authentication flows if needed
3. Add your models and migrations
4. Extend admin panel with custom features
5. Add feature-specific tests

## ✨ Next Steps

1. Review and update `app/Models/User.php`
2. Customize admin dashboard in `resources/views/admin/`
3. Add your business logic
4. Configure email settings in `.env`
5. Set up deployment environment
6. Run tests before deployment

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
