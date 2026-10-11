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
php artisan migrate --seed  # loads content/ via ContentSeeder

npm run dev                 # or: npm run build
```

With Herd the site is served at http://02-laravel-blog.test.

Saving a post queues a `ProcessPost` job (reading time, generated excerpt) on Redis, so keep a worker
running: `composer run dev` starts one alongside Vite, or run `php artisan queue:work`.

### Local admin

http://02-laravel-blog.test/admin/posts has create/edit/delete for posts. It only exists when
`APP_ENV=local` (404 anywhere else) and isn't part of the exported site. It edits the database only:
the Markdown files in `content/` remain the source of truth, and re-seeding overwrites posts with
matching slugs.

## Writing content

Posts and projects are Markdown files with YAML front matter:

```markdown
---
title: Building a travel assistant you can measure
project: travel-assistant      # optional, a project slug
tags: [AI, MCP, Evals]
published_at: 2026-11-01        # omit to keep it a draft
---

Body in GitHub-flavoured Markdown.
```

Save them in `content/posts/` or `content/projects/` and run `php artisan db:seed`. The seeder is
idempotent (upserts by slug), so re-running it only applies changes.

## Tests

```sh
php artisan test --compact
```

Tests run against in-memory SQLite with the array cache and sync queue (see `phpunit.xml`), so
Docker isn't needed for them. CI also runs the suite against MariaDB.

## Docs

- `docs/HANDOFF.md`: plan, decisions and phases
- `docs/LEARNING.md`: Laravel concepts used in each phase, written up for interview prep
