# OpenKab - Project Context

## Project Overview

**OpenKab** is a Laravel-based web application designed for county governments (Kabupaten) across Indonesia. It integrates with OpenSID to display public statistics including population, health, education, and other demographic data. The application promotes transparency and public information disclosure.

- **Repository**: https://github.com/OpenSID/OpenKab
- **Demo**: https://devopenkab.opendesa.id
- **Framework**: Laravel 10.x
- **PHP Version**: ^8.1
- **Architecture**: MVC (Model-View-Controller) with Laravel

## Tech Stack

### Backend
- **Framework**: Laravel 10.48
- **Language**: PHP 8.1+
- **Database**: MySQL (dual database setup - main app + OpenSID combined data)
- **Authentication**: Laravel Sanctum
- **Key Packages**:
  - `spatie/laravel-permission` - Role & permission management
  - `spatie/laravel-activitylog` - Activity logging
  - `yajra/laravel-datatables` - DataTables integration
  - `bensampo/laravel-enum` - Enum support
  - `intervention/image` - Image manipulation
  - `jeroennoten/laravel-adminlte` - AdminLTE integration
  - `kalnoy/nestedset` - Nested set model for hierarchies

### Frontend
- **Build Tool**: Vite 4.x
- **CSS Framework**: Bootstrap 4.6.2, AdminLTE 3.2.0
- **JavaScript**: jQuery, Alpine.js
- **UI Components**:
  - Select2, Bootstrap Datepicker, Bootstrap Colorpicker
  - SweetAlert2, TinyMCE, OWL Carousel
  - FontAwesome 6, Bootstrap Icons

### Testing
- **Unit/Feature Tests**: PHPUnit 10.x
- **E2E Tests**: Playwright (TypeScript)
- **Linting**: Laravel Pint, PHP CS Fixer

## Project Structure

```
OpenKab/
├── app/                    # Application logic
│   ├── Console/           # Artisan commands
│   ├── Enums/             # PHP enums
│   ├── Http/              # Controllers, Middleware, Requests
│   ├── Models/            # Eloquent models
│   ├── Policies/          # Authorization policies
│   ├── Services/          # Business logic services
│   └── View/              # View composers
├── bootstrap/             # Application bootstrap files
├── config/                # Configuration files
├── database/
│   ├── factories/         # Model factories for testing
│   ├── migrations/        # Database migrations
│   ├── seeders/           # Database seeders
│   └── maxmind/           # GeoIP database
├── docs/                  # Documentation (currently empty)
├── lang/                  # Localization files
├── public/                # Public assets (entry point)
├── resources/
│   ├── js/                # JavaScript source files
│   ├── sass/              # SCSS source files
│   └── views/             # Blade templates
├── routes/
│   ├── web.php            # Web routes
│   ├── api.php            # API routes
│   ├── apiv1.php          # API v1 routes
│   ├── console.php        # Console routes
│   └── breadcrumbs.php    # Breadcrumb definitions
├── storage/               # Logs, cache, uploads
├── tests/
│   ├── Unit/              # Unit tests
│   ├── Feature/           # Feature tests
│   ├── e2e/               # Playwright E2E tests
│   └── global-setup.js    # E2E test global setup
└── artisan                # Laravel CLI
```

## Building and Running

### Prerequisites
- PHP 8.1+ with required extensions
- Composer
- Node.js 18+
- MySQL 5.7+ or MariaDB
- Redis (optional)

### Installation

```bash
# 1. Install PHP dependencies
composer install

# 2. Install Node.js dependencies
npm install

# 3. Copy environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Configure database in .env file
# Edit DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 6. Run migrations
php artisan migrate

# 7. Seed database (optional)
php artisan db:seed

# 8. Build frontend assets
npm run build

# 9. Start development server
php artisan serve
```

### Development Commands

```bash
# Start Vite dev server (hot module replacement)
npm run dev

# Build production assets
npm run build

# Build for web (with config replacement)
npm run build-web

# Run PHPUnit tests
php artisan test

# Run E2E tests
npm run test:e2e

# Run E2E tests with UI
npm run test:e2e:ui

# Run E2E tests in headed mode
npm run test:e2e:headed

# Show E2E test report
npm run test:e2e:report

# Code style fixer
./vendor/bin/php-cs-fixer fix

# Laravel Pint (alternative linter)
./vendor/bin/pint
```

### Artisan Commands

```bash
# Standard Laravel commands available:
php artisan migrate          # Run migrations
php artisan migrate:fresh    # Fresh migrate
php artisan db:seed          # Seed database
php artisan make:model       # Generate model
php artisan make:controller  # Generate controller
php artisan make:request     # Generate form request
php artisan route:list       # List all routes
php artisan config:cache     # Cache configuration
php artisan view:clear       # Clear compiled views
```

## Configuration

### Environment Variables (from .env.example)

Key configuration options:

```ini
# Application
APP_NAME=OpenKab
APP_ENV=development
APP_DEBUG=false
APP_URL=http://devopenkab.opendesa.id/

# Main Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=testing_db
DB_USERNAME=root
DB_PASSWORD=secret

# Map Configuration
LATTITUDE_MAP=-8.459556
LONGITUDE_MAP=115.046600

# CSP (Content Security Policy)
CSP_ENABLED=true

# FTP Configuration (for data sync)
FTP_1_HOST=
FTP_1_URL=
FTP_1_USERNAME=
FTP_1_PASSWORD=
```

### Dual Database Setup

The application uses two database connections:
1. **Default (mysql)**: Main application data
2. **opensid (OPENKAB_*)**: Combined OpenSID data for statistics

## Testing

### Unit/Feature Tests
- Located in `tests/Unit` and `tests/Feature`
- Run with `php artisan test` or `./vendor/bin/phpunit`
- Uses in-memory SQLite or test MySQL database

### E2E Tests (Playwright)
- Located in `tests/e2e`
- Configuration in `playwright.config.js`
- Requires running application server
- Uses authentication state from `test-results/storage-state/auth.json`
- Global setup in `tests/global-setup.js`

### Test Credentials (Demo)
- **Email**: admin@gmail.com
- **Password**: Admin100%

## Development Conventions

### Code Style
- **PHP CS Fixer**: Configured in `.php-cs-fixer.php`
  - PSR-12 based with Laravel conventions
  - Short array syntax
  - Single quotes preferred
  - Blank lines between class methods/properties
- **Laravel Pint**: Alternative PHP linter

### File Organization
- Models in `app/Models/`
- Controllers in `app/Http/Controllers/`
- Form Requests in `app/Http/Requests/`
- Services in `app/Services/`
- Policies in `app/Policies/`

### Naming Conventions
- Models: PascalCase (e.g., `User`, `Penduduk`)
- Controllers: PascalCase with `Controller` suffix
- Migrations: `YYYY_MM_DD_HHMMSS_create_table_name.php`
- Routes: kebab-case for URL segments

### Database
- Migrations stored in `database/migrations/`
- Seeders in `database/seeders/`
- Factories in `database/factories/`
- Uses `eloquent-sluggable` for URL-friendly slugs
- Uses `nestedset` for hierarchical data

### Validation
- **All form validation MUST use Form Request classes** (located in `app/Http/Requests/`)
- Never validate directly in controllers using `$request->validate()`
- Create dedicated Form Request classes using `php artisan make:request <Name>Request`
- Form requests centralize validation rules and authorization logic

### Testing
- **DO NOT use `RefreshDatabase` trait** - it truncates tables and can cause issues with foreign key constraints
- **Use `DatabaseTransactions` trait instead** - wraps tests in database transactions
- Follow existing test patterns in `tests/Feature/` and `tests/Unit/`
- Use model factories for creating test data
- Test both success and failure scenarios
- **CORS Security Tests**: Located in `tests/Feature/CorsSecurityTest.php`
  - Tests verify CORS configuration is secure
  - Validates allowed origins are restricted (no wildcard with credentials)
  - Tests preflight requests from allowed/non-allowed origins
  - Run with: `php artisan test --filter CorsSecurityTest`

### Security
- Content Security Policy (CSP) enabled via `spatie/laravel-csp`
- Laravel Sanctum for API authentication
- Spatie Permission for role-based access control
- Activity logging via `spatie/laravel-activitylog`
- **CORS (Cross-Origin Resource Sharing)**:
  - Configured in `config/cors.php`
  - `allowed_origins` restricted to trusted domains only (via `CORS_ALLOWED_ORIGINS` env variable)
  - Never use wildcard (`*`) with `supports_credentials=true`
  - Default allowed origins: production domain + localhost for development
  - `allowed_headers` limited to: `Content-Type`, `Authorization`, `X-Requested-With`, `X-XSRF-TOKEN`

## API

### API Versions
- **v1**: Routes defined in `routes/apiv1.php`
- **Current**: Routes defined in `routes/api.php`

### Authentication
- Sanctum token-based authentication
- Stateful domains configured via `SANCTUM_STATEFUL_DOMAINS`

## Key Features

1. **Statistics Dashboard**: Population, health, education statistics
2. **Data Integration**: Sync with OpenSID databases
3. **User Management**: Role-based permissions
4. **Activity Logging**: Track user actions
5. **File Manager**: Integrated file management
6. **Breadcrumbs**: Navigation breadcrumbs
7. **Visitor Tracking**: Track page visits
8. **Location Services**: GeoIP-based location detection
9. **OTP System**: Two-factor authentication support
10. **Telegram Bot**: Notification integration

## Known Issues / Technical Notes

- N+1 query problems addressed in user management (Issue #943)
- Year filter added for statistics boards (Issues #946, #948)
- FTP integration for remote data synchronization
- Rate limiting configurable for OTP and general API

## Useful Links

- **Demo**: https://devopenkab.opendesa.id
- **GitHub**: https://github.com/OpenSID/OpenKab
- **Laravel Docs**: https://laravel.com/docs
- **AdminLTE**: https://adminlte.io/
