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

## Phase 3: Data + content

- **Migrations and foreign keys.** `foreignId('project_id')->nullable()->constrained()->nullOnDelete()`
  creates the column, the FK and its index in one line; deleting a project leaves its posts with a null
  project instead of deleting them. The `post_tag` pivot has a composite primary key and
  `cascadeOnDelete()` on both sides. `slug` is `unique()` (which is also its index) and `published_at`
  gets its own index because every public query filters and sorts on it.
- **Eloquent relationships and eager loading.** `Project hasMany Post`, `Post belongsTo Project`,
  `Post belongsToMany Tag`. Controllers call `->with('tags')` so a list of 20 posts runs 2 queries
  instead of 21 (the N+1 problem). `$post->load([...])` does the same for an already-fetched model.
- **Local scopes with `#[Scope]`.** `Post::published()` keeps "visible to the public" in one place
  (`published_at <= now()`, newest first) and composes through relationships:
  `$tag->posts()->published()`, `whereHas('posts', fn ($q) => $q->published())`. Local rather than
  global, so the admin area (Phase 4) still sees drafts.
- **Casts and enums.** `published_at` is cast to Carbon, `featured` to bool, and `status` to a backed
  `ProjectStatus` enum, so templates call `$project->status->label()` and invalid values fail loudly.
  Laravel 13 declares `#[Fillable([...])]` as a class attribute instead of a `$fillable` property.
- **Attribute casting (`array` / JSON).** `projects.stack` is a nullable `json` column cast to `array`:
  Eloquent `json_encode`s the PHP array on save and decodes it on read, so code and templates only ever
  see `['Laravel', 'Redis']`, and `null` stays `null`. Casts live in the model's `casts()` method and run
  in both directions (get and set). Use `AsArrayObject`/`AsCollection` when you need to mutate items in
  place (a plain `array` cast returns a copy), and a custom `CastsAttributes` class for value objects.
  MariaDB stores `json` as `LONGTEXT` with a `JSON_VALID` check; SQLite stores it as text.
- **Route model binding by slug.** `getRouteKeyName()` returns `slug`, so `Route::resource('posts', ...)`
  resolves `/posts/{post}` by slug and 404s automatically when no row matches; `route('posts.show', $post)`
  builds the URL from the model. The controller still 404s drafts explicitly (`abort_unless`).
- **Seeders, factories and idempotency.** `ContentSeeder` upserts by slug with `updateOrCreate`, so it
  can run on every deploy; unchanged records aren't saved, so `updated_at` (and the cache key) stay
  put. Front matter is checked with the `Validator` facade outside a request. Factories have states
  (`draft()`, `scheduled()`, `featured()`, `archived()`) so tests say what they mean.
- **Cache-aside with versioned keys.** `Cache::remember("markdown.posts.{slug}.{updated_at}", ...)`
  stores rendered HTML in Redis. Including `updated_at` in the key means an edit naturally misses the
  cache, so there's no explicit invalidation to forget; old keys just expire after a week.
