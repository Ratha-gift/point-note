# Point Note

A small daily-sales tracker: log how much you sold each day, and it converts
that into "points" (amount ÷ 5) with a per-day view and a monthly summary
that can be exported to PDF or Excel.

## What it does

- **Daily entry** (`/point-tracker`) — pick a date, type an amount (or a
  `+`-joined expression like `30+40+60` to sum several sales in one save),
  and see the running total and point count for that day. Entries can be
  edited or deleted individually.
- **Monthly report** (`/point-tracker/report`) — per-day totals and points
  for a chosen month, with buttons to export the month as PDF or `.xlsx`.

## Tech stack

- **Backend:** Laravel 10 (PHP 8.1+), MySQL
- **Frontend:** React 19 via Inertia.js v2, Vite, Tailwind CSS
- **Exports:** `barryvdh/laravel-dompdf` (PDF), `phpoffice/phpspreadsheet` (Excel)

## Local development

Requirements: PHP 8.1+, Composer, Node.js, MySQL.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Set `DB_*` in `.env` to point at a local MySQL database, then:

```bash
php artisan migrate
npm run dev        # in one terminal — Vite dev server with HMR
php artisan serve  # in another terminal
```

Visit `http://127.0.0.1:8000/point-tracker`.

## Deployment

```bash
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
```

Set the following in the production environment before running the above
(see `.env.example` for the full list):

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` (generate with
  `php artisan key:generate` if not already set), `APP_URL`
- `DB_*` — your production database credentials
- `SESSION_DRIVER=database`, `CACHE_DRIVER=database`,
  `QUEUE_CONNECTION=database` — these require the `sessions`, `cache`, and
  `jobs` tables, which are already included in `database/migrations/` and
  will be created by `php artisan migrate --force`

`npm run build` outputs to `public/build/` and is what Blade's `@vite`
directive serves in production — no Node process needs to keep running on
the server.
