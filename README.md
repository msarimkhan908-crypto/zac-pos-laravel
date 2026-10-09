# ZAC Burger Club — POS

A full-stack point-of-sale system for ZAC Burger Club, built with Laravel, Livewire, Alpine.js, and Tailwind CSS.

## Tech Stack

- **Backend:** Laravel 13, PHP 8.4
- **Frontend:** Blade + Livewire 4 + Alpine.js
- **Styling:** Tailwind CSS (Vite), dark & light mode
- **Auth:** Laravel Breeze (Blade, password login)
- **Database:** MySQL

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+ and npm
- MySQL 8.0+

## Setup

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Create the database

Create an empty MySQL database named `zac_pos`:

```sql
CREATE DATABASE zac_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Configure the environment

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zac_pos
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run migrations and seed

```bash
php artisan migrate
php artisan db:seed
```

This creates the admin account:
- **Email:** admin@zac.test
- **Password:** Admin@12345

### 5. Create the storage symlink

```bash
php artisan storage:link
```

This is required for any logo or image uploads added later.

### 6. Build frontend assets

```bash
npm run build
```

For hot-reload during development:

```bash
npm run dev
```

### 7. Start the server

```bash
php artisan serve
```

Open [http://localhost:8000](http://localhost:8000) in your browser.

## Roles

| Role     | Access                                         |
|----------|------------------------------------------------|
| admin    | All sections                                   |
| cashier  | POS, Orders                                    |
| kitchen  | Orders                                         |

Only admins can create new user accounts. There is no public registration page.

## Default Settings

Seeded via `SettingsSeeder`:

| Key                      | Default Value    |
|--------------------------|------------------|
| restaurant_name          | ZAC Burger Club  |
| currency                 | USD              |
| currency_symbol          | $                |
| default_delivery_charge  | 5.00             |

## Project Structure

```
app/
  Http/
    Controllers/      — DashboardController, ProfileController, Auth/*
    Middleware/        — RoleMiddleware
  Models/             — User, Setting
database/
  migrations/         — users, cache, jobs, settings
  seeders/            — AdminUserSeeder, SettingsSeeder
resources/views/
  layouts/            — app.blade.php, guest.blade.php
  components/         — sidebar-link, coming-soon, Breeze components
  auth/               — login, forgot-password, etc.
  dashboard.blade.php
  pos/ orders/ products/ inventory/
  receipt-designer/ reports/ employees/ settings/
routes/
  web.php             — all app routes
  auth.php            — login / logout / password reset
```
