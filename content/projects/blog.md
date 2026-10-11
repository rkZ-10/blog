---
name: This blog
tagline: A Laravel 13 app with MariaDB, Redis and queued jobs, exported to static HTML for GitHub Pages.
stack: [Laravel, MariaDB, Redis, Pest]
repo_url: https://github.com/rkZ-10/blog
status: active
featured: true
sort_order: 2
---

The site you're reading. It's a regular Laravel app (Eloquent models, Blade components, a Redis cache
for rendered Markdown, a queued job, Pest tests) that gets crawled into static HTML and deployed to
GitHub Pages by GitHub Actions.

Posts and projects live as Markdown files with YAML front matter and are loaded into MariaDB by an
idempotent seeder.
