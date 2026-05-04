# Game Management System (gamemgt)

This project is a game management system built with Laravel 12.0. It manages game types, tables, presets, payouts, and financial tracking (ledger/float).

## Tech Stack
- **Framework:** Laravel 12.0
- **PHP:** ^8.2
- **Frontend:**
  - **Templating:** Blade
  - **Scripting:** Alpine.js, Axios
  - **Styling:** Bootstrap 5
- **Database:** MySQL/MariaDB (Eloquent ORM)
- **Testing:** Pest PHP
- **Auth:** Laravel Breeze (Session-based for Web, Sanctum for API)

## Architecture & Design Patterns

### Role-Based Access Control (RBAC)
- Custom implementation using `Role` and `Permission` models.
- Middleware: `CheckPermission.php` (alias: `permission`).
- Usage in routes: `->middleware('permission:view-users')`.

### Game Presets & Configuration
- Each `GameTable` has a `GameTableConfig` which links to a specific game preset (e.g., `BaccaratPreset`, `BlackjackPreset`).
- Strategy Pattern: `GameTableController::resolvePresetModel()` handles fetching the correct preset model based on the game type code.

### API Response Formatting
- **Trait:** `FormatsGameTable` is used to provide consistent, deeply nested JSON responses for game tables across different game types.
- API Versioning: Routes are prefixed with `v1`.

### Financial Tracking
- **Table Float:** Manages opening and closing balances for tables.
- **Table Ledger:** Tracks transactions (fills, credits, etc.) with a status-based workflow (pending, claimed, completed).

## Directory Structure & Conventions

- `app/Models/`: Eloquent models with clear relationships and `$casts`.
- `app/Http/Controllers/`: Standard Laravel controllers. API-specific logic is often prefixed with `api` in method names (e.g., `apiIndex`).
- `app/FormatsGameTable.php`: Trait for complex response formatting.
- `database/migrations/`: Standard migrations. Note the use of `xxxx_create_game_history_tables.php` for dynamic game history.
- `resources/views/`: Organized by entity (chips, game_tables, payout_rules, etc.).

## Coding Standards

### PHP
- Strict typing where possible.
- PSR-12 coding standard (enforced via Laravel Pint).
- Use of PHP 8.2+ features like `match` expressions and null-safe operators.

### Laravel
- Use Eloquent for all database interactions.
- Resource controllers for standard CRUD operations.
- Custom validation rules (e.g., `PipeSeparatedNumbers`).

### Frontend
<!-- - **Tailwind CSS:** Primary styling framework. -->
- **Alpine.js:** Used for lightweight interactivity in Blade templates.
- **Axios:** Used for API calls from the frontend.

## Workflows

### Database
- Use migrations for all schema changes.
- Seeders are used for initial setup (`DatabaseSeeder`, `RolePermissionSeeder`, etc.).
- Use `php artisan migrate --seed` for fresh installations.

### Testing
- Run tests using `php artisan test` or `./vendor/bin/pest`.
- Feature tests are located in `tests/Feature`.
- Unit tests are located in `tests/Unit`.

### API Documentation
- Postman documentation link found in `routes/api.php`.

## Environment Setup
1. Copy `.env.example` to `.env`.
2. Configure database credentials.
3. Run `composer install` and `npm install`.
4. Run `php artisan key:generate`.
5. Run `php artisan migrate --seed`.
6. Run `npm run dev` or `npm run build`.
