# Handoff: Laravel blog (rkz-10.github.io/blog)

You're picking up a freshly created Laravel 13 app in `D:\My Projects\02-laravel-blog`. This file is the
plan agreed with Arkesh (the owner) in a planning session. Read it fully before writing code, then work
phase by phase and stop for review at the end of each phase.

## 1. Why this project exists

- A personal blog + projects site for Arkesh, linked both ways with his existing portfolio
  (https://rkz-10.github.io/, repo `rkZ-10/rkZ-10.github.io`, local folder `D:\My Projects\portfolio`).
- Learning project: built to practise production Laravel with a common backend stack (MariaDB, Redis; MongoDB optional; Solr via Scout concepts).
- He knows backend well (PHP background, Go, PostgreSQL, SQL Server, Redis, DynamoDB) but is refreshing
  Laravel specifically. Explain Laravel-specific choices briefly as you go (see `docs/LEARNING.md`, §8).

## 2. Locked decisions

| Topic | Decision |
|---|---|
| Framework | Laravel 13, PHP 8.4 (via Laravel Herd on Windows), Blade, no starter kit |
| Tests | Pest (ships with Laravel 13; runs on PHPUnit). Feature tests for every page and rule; `LEARNING.md` shows the PHPUnit-class equivalent for reference |
| AI tooling | Laravel Boost is installed (guidelines, skills, MCP). Keep its section of `CLAUDE.md` intact; add project notes **outside** the `<laravel-boost-guidelines>` block |
| Hosting | **Static export** of the Laravel app to **GitHub Pages**, served at **`https://rkz-10.github.io/blog`** |
| Export tool | `spatie/laravel-export` (crawls the app, writes static HTML + assets). Check its current README for Laravel 13 support before installing |
| Repo | GitHub repo **`rkZ-10/blog`** (the repo name *is* the URL path). It must be **public**: Pages on private repos needs a paid plan |
| Main DB | **MariaDB** (Laravel's `mariadb` driver) via Docker locally and as a GitHub Actions service |
| Cache + queue | **Redis** (cache rendered Markdown; queue a post-processing job) |
| MongoDB | Optional stretch phase only (post revision history via `mongodb/laravel-mongodb`) |
| Solr | Not implemented (a static site can't query a search server). Cover Laravel Scout concepts in the Laravel blog post instead |
| Comments | None for now (Giscus may come later) |
| Portfolio | Stays as is for now; blog matches its theme and links to it. Moving the portfolio into Laravel is a later decision |

Not available on Herd's free tier: managed MariaDB/Redis services. Use **Docker Desktop** for those.

## 3. Look and feel: match the portfolio exactly

Copy these tokens from the portfolio (`D:\My Projects\portfolio\index.html`) into the app's CSS so the
two sites feel like one. Read that file for spacing, eyebrow labels, cards and dark-mode handling.

```css
/* fonts */
https://fonts.googleapis.com/css2?family=Spectral:ital,wght@0,400;0,500;0,600;1,400&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap
headings: "Spectral", Georgia, serif        body: "IBM Plex Sans", ui-sans-serif, system-ui, sans-serif
labels/code: "IBM Plex Mono", ui-monospace, monospace

/* light */ --bg:#f5f6f4; --surface:#ffffff; --fg:#13181a; --muted:#59636a;
            --accent:#0f766e; --accent-soft:#e3efed; --border:#dbe0dd; --mono-chip:#eef1ef;
/* dark  */ --bg:#0c1312; --surface:#121b19; --fg:#e9ece9; --muted:#8fa09a;
            --accent:#34d1bb; --accent-soft:#163631; --border:#223430; --mono-chip:#182622;
```

- Dark mode exactly like the portfolio: `prefers-color-scheme` guarded by `:root:not([data-theme="light"])`,
  plus `:root[data-theme="dark"]`.
- Editorial single column (~760px text, ~980px wide sections), Spectral headings, mono uppercase
  "eyebrow" labels in the accent colour.
- Header nav: **Blog · Projects · Portfolio ↗ · Résumé**. Portfolio links to https://rkz-10.github.io/.
- Use Tailwind (it ships with Laravel 13) with these tokens mapped as theme colours, or plain CSS.
  Either is fine; keep one approach.

**Privacy:** the portfolio shows a phone number and is `noindex`. The blog shows **email only**
(arkeshjayakumar@gmail.com) and should be indexable (sitemap + robots allow). Confirm with Arkesh
before launch. The downloadable résumé must be a version without the phone number; Arkesh provides it.

## 4. Data model

- `projects`: id, name, slug (unique), tagline, summary (markdown), repo_url, live_url (nullable),
  status (enum-like string: active/archived), featured (bool), sort_order, timestamps.
- `posts`: id, project_id (nullable FK → projects, null on delete), title, slug (unique), excerpt,
  body_markdown (longText), reading_minutes (nullable), published_at (nullable = draft), timestamps.
  Indexes on slug and published_at.
- `tags` + `post_tag` pivot (many-to-many).
- Models: `Project hasMany Post`, `Post belongsTo Project`, `Post belongsToMany Tag`.
  Scopes: `Post::published()` (published_at <= now), ordered newest first.
- **Content source of truth:** Markdown files with YAML front matter in `content/posts/*.md` and
  `content/projects/*.md`, loaded by a `ContentSeeder` (idempotent: upsert by slug). The admin area
  (below) edits the database for practice; the files remain what gets committed.
- Markdown → HTML with `league/commonmark` (GFM tables, fenced code, heading anchors). Cache the
  rendered HTML in **Redis** (`Cache::remember`, key includes slug + updated_at).

## 5. Behaviour to build

- **Pages:** `/` (intro, featured projects, latest posts), `/posts`, `/posts/{slug}`, `/projects`,
  `/projects/{slug}`, `/tags/{slug}`, résumé PDF at a stable path (e.g. `/resume.pdf` from `public/`),
  `/feed.xml` (RSS), `/sitemap.xml`, a styled 404.
- **Queue job:** `ProcessPost` dispatched from a model observer when a post is saved: computes
  reading_minutes and fills the excerpt if empty. Redis queue locally; `sync` in CI/export.
- **Local-only admin:** `/admin/posts` CRUD with `StorePostRequest`/`UpdatePostRequest` validation
  (unique slug, required title/body, valid date). Protected by middleware that 404s unless
  `app()->isLocal()`. Never linked from public pages and **excluded from the export**.
- **Base path:** everything must work under `/blog`. Use `route()`, `url()`, `asset()` and Vite
  helpers only; never hard-code `/` paths. Export with `APP_URL=https://rkz-10.github.io/blog`.
  Add `.nojekyll` and `404.html` to the export output.

## 6. Local environment

- `docker-compose.yml` with `mariadb` (current LTS image) and `redis`, named volumes, ports 3306/6379.
- `.env`: `DB_CONNECTION=mariadb`, `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`, `REDIS_CLIENT` set to
  whatever works on Herd for Windows (check whether the `phpredis` extension is available; otherwise
  install `predis/predis`). Keep `.env.example` in sync. Never commit `.env`.
- Tests (Pest): `phpunit.xml` uses SQLite in memory for speed locally; CI runs the suite against MariaDB too.
- Herd serves the app at `http://02-laravel-blog.test`.

## 7. Phases (stop after each for Arkesh to review and commit)

1. **Repo + environment:** `git init`, sensible `.gitignore` check, `docker-compose.yml`, switch `.env`
   to MariaDB + Redis, `php artisan migrate`, tests green. Add a short project section to `CLAUDE.md`
   (outside the Boost block) and a `README.md` describing the project.
2. **Theme + layout:** Blade layout and components (header/nav, footer, card, eyebrow, prose styles)
   matching the portfolio in light and dark. Home page with placeholder content.
3. **Data + content:** migrations, models, factories, `ContentSeeder`, Markdown rendering with Redis
   cache, posts/projects/tags pages, feed, sitemap. Feature tests for every route (published vs draft,
   404 for unknown slugs, tag filtering).
4. **Queue + admin:** observer + `ProcessPost` job, local-only admin CRUD with form requests. Tests for
   validation rules and the local-only middleware.
5. **Export + deploy:** `spatie/laravel-export` config (exclude `/admin`), base path, `.nojekyll`,
   `404.html`. GitHub Actions workflow on push to `main`: PHP 8.4 + Node, MariaDB and Redis services,
   `composer install`, `npm ci && npm run build`, migrate + seed, run tests, export, deploy with
   `actions/upload-pages-artifact` + `actions/deploy-pages`. Arkesh creates the public `rkZ-10/blog`
   repo and enables Pages (source: GitHub Actions).
6. **Content:**
   - Post 1: **Travel Assistant**. Source material: `D:\My Projects\01-travel-assistant` (README,
     `docs/`, `docs/images/` screenshots, `evals/baselines/`) and the study guide PDFs in
     `D:\My Projects`. Angle: what it does, architecture (MCP server, hybrid RAG, Agent SDK, evals,
     FastAPI + React), and 2-3 real problems hit (UpFront fee bug, SpiceJet false pass, booking token
     garbled). Link https://github.com/rkZ-10/travel-assistant. Also a `projects` entry.
   - Post 2: **Building this site with Laravel** (after launch): static export, MariaDB/Redis choices,
     queue job, tests, what Scout/Solr would add on a live server.
7. **Cross-links:** add a "Blog" link to the portfolio header (`D:\My Projects\portfolio\index.html`,
   separate repo), matching its style.
8. *(Optional)* **MongoDB:** `mongodb/laravel-mongodb` for a `post_revisions` collection written by the
   observer. First check that the `mongodb` PHP extension can be enabled in Herd on Windows; if not, run
   it only in CI/Docker or skip.

## 8. Working conventions

- Small commits per phase. Arkesh reviews and pushes. Use the commit attribution lines your environment
  provides.
- Keep `docs/LEARNING.md` as you go: for each phase, 3-6 bullets of "Laravel concepts used and how to
  explain them" (e.g. service container, Eloquent relationships, observers vs events,
  queues, form requests, middleware, config caching). This is part of the deliverable.
- Keep `README.md` and `CLAUDE.md` current as things change.
- Prefer Laravel conventions and Boost guidance; run `php artisan test` before saying a phase is done.
- No secrets in the repo; no phone number anywhere on the site.
