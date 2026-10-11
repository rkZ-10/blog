<?php

use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Services\PublicPages;

it('lists every public page for the export and nothing private', function () {
    $project = Project::factory()->create(['slug' => 'a-project']);
    $usedTag = Tag::factory()->create(['slug' => 'used-tag']);
    $draftOnlyTag = Tag::factory()->create(['slug' => 'draft-only-tag']);
    Post::factory()->hasAttached($usedTag)->create(['slug' => 'live-post']);
    Post::factory()->draft()->hasAttached($draftOnlyTag)->create(['slug' => 'draft-post']);

    $urls = app(PublicPages::class)->exportUrls();

    expect($urls)->toEqualCanonicalizing([
        route('home'),
        route('posts.index'),
        route('projects.index'),
        route('feed'),
        route('sitemap'),
        route('not-found'),
        route('posts.show', 'live-post'),
        route('projects.show', $project),
        route('tags.show', $usedTag),
    ]);
});

it('never includes the admin area or the health check', function () {
    Post::factory()->create();

    $paths = collect(app(PublicPages::class)->exportUrls())->map(fn (string $url) => parse_url($url, PHP_URL_PATH) ?? '/');

    expect($paths->filter(fn (string $path) => str_starts_with($path, '/admin') || $path === '/up'))->toBeEmpty();
});
