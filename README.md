# blog

Source for Arkesh Jayakumar's blog and projects site, live at **https://rkz-10.github.io/blog**
(companion to the portfolio at https://rkz-10.github.io/).

It's a regular Laravel 13 app (Eloquent, Blade, Redis cache, queued jobs, Pest tests) that gets
exported to static HTML and deployed to GitHub Pages. Posts and projects are Markdown files with
front matter in `content/`, seeded into MariaDB.

## Stack

- Laravel 13, PHP 8.4, Blade, Tailwind CSS 4, Vite
- MariaDB 12.3 (main database) and Redis 7 (cache + queue), run locally with Docker
- Pest for tests
- `spatie/laravel-export` + GitHub Actions for the static build and deploy (planned)

## Local setup

Requirements: PHP 8.4 with the `redis` extension (Laravel Herd works), Composer, Node, Docker Desktop.

```sh
composer install
npm install
cp .env.example .env
php artisan key:generate

docker compose up -d        # MariaDB on 3306, Redis on 6379
php artisan migrate

npm run dev                 # or: npm run build
```

With Herd the site is served at http://02-laravel-blog.test.

## Tests

```sh
php artisan test --compact
```

Tests run against in-memory SQLite with the array cache and sync queue (see `phpunit.xml`), so
Docker isn't needed for them. CI also runs the suite against MariaDB.

## Docs

- `docs/HANDOFF.md`: plan, decisions and phases
- `docs/LEARNING.md`: Laravel concepts used in each phase, written up for interview prep
