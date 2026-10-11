<?php

use App\Enums\ProjectStatus;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use Database\Seeders\ContentSeeder;

beforeEach(function () {
    config(['blog.content_path' => base_path('tests/Fixtures/content')]);
});

it('creates projects from front matter with the body as the summary', function () {
    $this->seed(ContentSeeder::class);

    $project = Project::where('slug', 'sample-project')->sole();
    expect($project)
        ->name->toBe('Sample Project')
        ->tagline->toBe('A project used by the ContentSeeder tests.')
        ->summary->toBe('The **summary** of the sample project.')
        ->stack->toBe(['Laravel', 'Redis'])
        ->repo_url->toBe('https://github.com/example/sample-project')
        ->live_url->toBe('https://example.com')
        ->status->toBe(ProjectStatus::Archived)
        ->featured->toBeTrue()
        ->sort_order->toBe(3);
});

it('creates posts linked to their project and tags', function () {
    $this->seed(ContentSeeder::class);

    $post = Post::where('slug', 'first-post')->sole();
    expect($post)
        ->title->toBe('First post')
        ->excerpt->toBe("The first post's excerpt.")
        ->body_markdown->toBe("## Hello\n\nBody of the first post.");
    expect($post->project->slug)->toBe('sample-project');
    expect($post->tags->pluck('slug')->all())->toBe(['laravel', 'testing']);
});

it('reads unquoted dates, quoted datetimes and missing dates', function () {
    $this->seed(ContentSeeder::class);

    expect(Post::where('slug', 'first-post')->sole()->published_at->toDateTimeString())->toBe('2026-01-15 00:00:00');
    expect(Post::where('slug', 'second-post')->sole()->published_at->toDateTimeString())->toBe('2026-02-01 09:30:00');
    expect(Post::where('slug', 'custom-draft-slug')->sole()->published_at)->toBeNull();
});

it('updates existing records by slug instead of duplicating them', function () {
    Post::factory()->create(['slug' => 'first-post', 'title' => 'Stale title']);

    $this->seed(ContentSeeder::class);
    $this->seed(ContentSeeder::class);

    expect(Post::count())->toBe(3);
    expect(Project::count())->toBe(1);
    expect(Tag::count())->toBe(2);
    expect(Post::where('slug', 'first-post')->value('title'))->toBe('First post');
});

it('generates missing excerpts and reading times through the queued job', function () {
    $this->seed(ContentSeeder::class);

    expect(Post::where('slug', 'second-post')->sole())
        ->excerpt->toBe('Body of the second post.')
        ->reading_minutes->toBe(1);
});

it('leaves unchanged records untouched when re-seeded', function () {
    $this->seed(ContentSeeder::class);
    $updatedAt = Post::where('slug', 'second-post')->sole()->updated_at;

    $this->travel(1)->minutes();
    $this->seed(ContentSeeder::class);

    expect(Post::where('slug', 'second-post')->sole())
        ->updated_at->toEqual($updatedAt)
        ->excerpt->toBe('Body of the second post.');
});

it('rejects a post without a title and names the file', function () {
    config(['blog.content_path' => base_path('tests/Fixtures/content-invalid')]);

    $this->seed(ContentSeeder::class);
})->throws(InvalidArgumentException::class, 'missing-title.md: The title field is required.');
