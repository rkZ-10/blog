# Learning log

Laravel concepts used in each phase, and how to explain them in an interview.

## Phase 1: Repo + environment

- **`.env` and config files.** `.env` holds per-environment values; `config/*.php` reads them with
  `env()` and is the only place that should. App code calls `config('cache.default')`, never `env()`,
  because after `php artisan config:cache` the `.env` file isn't loaded at all and `env()` returns null.
- **Drivers behind one API.** `DB_CONNECTION=mariadb`, `CACHE_STORE=redis` and `QUEUE_CONNECTION=redis`
  swap implementations without touching code: `Cache::get()` or `dispatch()` resolve a store/connection
  through a manager class (`CacheManager`, `QueueManager`) bound in the service container. Tests switch
  to `array`/`sync`/SQLite purely through `phpunit.xml` env overrides.
- **The `mariadb` driver.** Laravel 11+ has a dedicated `mariadb` connection (it extends the MySQL one)
  so the schema builder and grammar can use MariaDB-specific behaviour (e.g. native `uuid` columns)
  instead of pretending it's MySQL.
- **Migrations.** Versioned, ordered PHP classes with `up()`/`down()`; `php artisan migrate` records
  what ran in the `migrations` table, so each environment converges to the same schema. The default
  migrations already create the `cache`, `jobs` and `sessions` tables for the database-backed drivers.
- **phpredis vs predis.** `REDIS_CLIENT=phpredis` uses the C extension (faster, needs the extension
  installed); `predis/predis` is a pure-PHP fallback for hosts where you can't add extensions.

## Phase 2: Theme + layout

- **Anonymous Blade components.** A file in `resources/views/components/` becomes a tag:
  `components/layouts/app.blade.php` is `<x-layouts.app>`, `components/card.blade.php` is `<x-card>`.
  No PHP class is needed when there's no logic; `@props` declares inputs and their defaults, and
  everything else lands in `$attributes`. Reach for a class-based component when it needs injected
  services or non-trivial logic.
- **Slots and attribute merging.** `{{ $slot }}` is the default content; named slots
  (`<x-slot:aside>`) fill extra regions. `$attributes->class([...])` merges the caller's classes with
  the component's, so `<x-card class="flex">` keeps the card's own styling. `@class` builds
  conditional class lists (used for the active nav link).
- **Layouts as components vs `@extends`.** Laravel supports both template inheritance
  (`@extends`/`@section`/`@yield`) and component layouts. Component layouts pass data as props
  (`<x-layouts.app title="Projects">`), which is explicit and type-checkable; inheritance relies on
  named sections. Modern starter kits use components.
- **Invokable controllers and named routes.** `HomeController` has a single `__invoke` method, routed
  with `Route::get('/', HomeController::class)->name('home')`. Views link with `route('home')`, so URLs
  can change (or move under `/blog`) without touching templates. `Route::view()` serves a view with
  no controller at all.
- **Vite integration.** `@vite` reads `public/build/manifest.json` to emit hashed asset URLs (or points
  at the dev server when `public/hot` exists). The Laravel Vite plugin's `fonts` option downloads
  Google Fonts at build time and `@fonts` inlines the `@font-face` rules and preloads. Feature tests
  call `withoutVite()` so they don't need a frontend build.
- **Custom error pages.** Dropping `resources/views/errors/404.blade.php` in place is enough: the
  exception handler renders the view matching the HTTP status, with the normal layout.
