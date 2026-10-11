<?php

use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

beforeEach(function () {
    $this->app['env'] = 'local';

    // Laravel only skips CSRF checks while APP_ENV is "testing"; switching to "local" re-enables them.
    $this->withoutMiddleware(PreventRequestForgery::class);
});

/**
 * @return array<string, mixed>
 */
function validPostInput(array $overrides = []): array
{
    return array_merge([
        'title' => 'Queues in Laravel',
        'slug' => '',
        'excerpt' => '',
        'body_markdown' => 'Some **body** text.',
        'project_id' => '',
        'tags' => 'Laravel, Redis',
        'published_at' => '',
    ], $overrides);
}

describe('index', function () {
    it('lists drafts, scheduled and published posts with their status', function () {
        Post::factory()->create(['title' => 'Published one', 'published_at' => '2026-01-02 10:00:00']);
        Post::factory()->draft()->create(['title' => 'Draft one']);
        Post::factory()->create(['title' => 'Scheduled one', 'published_at' => now()->addDay()]);

        $response = $this->get(route('admin.posts.index'));

        $response->assertOk()
            ->assertSeeInOrder(['Published one', 'Published 2 Jan 2026'])
            ->assertSeeInOrder(['Draft one', 'Draft'])
            ->assertSeeInOrder(['Scheduled one', 'Scheduled']);
    });
});

describe('store', function () {
    it('creates a post with its tags and redirects to the edit page', function () {
        $project = Project::factory()->create();

        $response = $this->post(route('admin.posts.store'), validPostInput([
            'project_id' => $project->id,
            'published_at' => '2026-05-06T07:08',
        ]));

        $post = Post::where('slug', 'queues-in-laravel')->sole();
        $response->assertRedirect(route('admin.posts.edit', $post))->assertSessionHas('status', 'Post created.');
        expect($post)
            ->title->toBe('Queues in Laravel')
            ->body_markdown->toBe('Some **body** text.')
            ->project_id->toBe($project->id)
            ->published_at->toDateTimeString()->toBe('2026-05-06 07:08:00');
        expect($post->tags->pluck('slug')->all())->toBe(['laravel', 'redis']);
    });

    it('runs the processing job, filling the excerpt and reading time', function () {
        $this->post(route('admin.posts.store'), validPostInput(['excerpt' => '']));

        $post = Post::where('slug', 'queues-in-laravel')->sole();
        expect($post)
            ->excerpt->toBe('Some body text.')
            ->reading_minutes->toBe(1);
    });

    it('normalises a typed slug and reuses existing tags case-insensitively', function () {
        Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);

        $this->post(route('admin.posts.store'), validPostInput([
            'slug' => 'My Custom Slug',
            'tags' => 'laravel, LARAVEL, , Queues',
        ]));

        $post = Post::where('slug', 'my-custom-slug')->sole();
        expect($post->tags->pluck('slug')->all())->toBe(['laravel', 'queues']);
        expect(Tag::count())->toBe(2);
    });

    it('rejects invalid input without creating a post', function (array $input, string $field, string $message) {
        Post::factory()->create(['slug' => 'taken-slug']);

        $response = $this->from(route('admin.posts.create'))->post(route('admin.posts.store'), validPostInput($input));

        $response->assertRedirect(route('admin.posts.create'))->assertSessionHasErrors([$field => $message]);
        expect(Post::count())->toBe(1);
    })->with([
        'missing title' => [['title' => ''], 'title', 'The title field is required.'],
        'missing body' => [['body_markdown' => ''], 'body_markdown', 'The body field is required.'],
        'duplicate slug' => [['slug' => 'taken-slug'], 'slug', 'The slug has already been taken.'],
        'invalid date' => [['published_at' => 'not a date'], 'published_at', 'The publish date field must be a valid date.'],
        'unknown project' => [['project_id' => 999], 'project_id', 'The selected project is invalid.'],
        'long excerpt' => [['excerpt' => str_repeat('a', 501)], 'excerpt', 'The excerpt field must not be greater than 500 characters.'],
    ]);
});

describe('update', function () {
    it('updates the post and replaces its tags', function () {
        $post = Post::factory()->hasAttached(Tag::factory()->state(['name' => 'Old', 'slug' => 'old']))->create();

        $response = $this->put(route('admin.posts.update', $post), validPostInput([
            'title' => 'Renamed post',
            'slug' => $post->slug,
            'tags' => 'New',
        ]));

        $response->assertRedirect(route('admin.posts.edit', $post))->assertSessionHas('status', 'Post saved.');
        expect($post->refresh()->title)->toBe('Renamed post');
        expect($post->tags->pluck('slug')->all())->toBe(['new']);
    });

    it('keeps the post as a draft when the publish date is cleared', function () {
        $post = Post::factory()->create();

        $this->put(route('admin.posts.update', $post), validPostInput(['slug' => $post->slug, 'published_at' => '']));

        expect($post->refresh()->published_at)->toBeNull();
    });

    it('rejects a slug that belongs to another post', function () {
        Post::factory()->create(['slug' => 'other-post']);
        $post = Post::factory()->create(['slug' => 'this-post', 'title' => 'Original']);

        $response = $this->put(route('admin.posts.update', $post), validPostInput(['slug' => 'other-post']));

        $response->assertSessionHasErrors(['slug' => 'The slug has already been taken.']);
        expect($post->refresh())->slug->toBe('this-post')->title->toBe('Original');
    });
});

describe('destroy', function () {
    it('deletes the post but keeps its tags', function () {
        $tag = Tag::factory()->create();
        $post = Post::factory()->hasAttached($tag)->create();

        $response = $this->delete(route('admin.posts.destroy', $post));

        $response->assertRedirect(route('admin.posts.index'));
        $this->assertModelMissing($post);
        $this->assertModelExists($tag);
        $this->assertDatabaseMissing('post_tag', ['post_id' => $post->id]);
    });
});

it('is never linked from public pages', function () {
    Post::factory()->create();

    $response = $this->get(route('home'));

    $response->assertOk()->assertDontSee('/admin', escape: false);
});
