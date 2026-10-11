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
