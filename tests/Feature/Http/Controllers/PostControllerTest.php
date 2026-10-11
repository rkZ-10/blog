<?php

use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;

describe('index', function () {
    it('lists published posts newest first', function () {
        Post::factory()->create(['title' => 'Older post', 'published_at' => now()->subMonth()]);
        Post::factory()->create(['title' => 'Newer post', 'published_at' => now()->subDay()]);

        $response = $this->get(route('posts.index'));

        $response->assertOk()->assertSeeInOrder(['Newer post', 'Older post']);
    });

    it('excludes drafts and scheduled posts', function () {
        Post::factory()->draft()->create(['title' => 'Draft post']);
        Post::factory()->scheduled()->create(['title' => 'Scheduled post']);

        $response = $this->get(route('posts.index'));

        $response->assertOk()
            ->assertDontSee('Draft post')
            ->assertDontSee('Scheduled post')
            ->assertSee('No posts yet.');
    });
});

describe('show', function () {
    it('renders a published post with its markdown body, tags and project', function () {
        $project = Project::factory()->create(['name' => 'Linked Project']);
        $tag = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
        $post = Post::factory()->for($project)->hasAttached($tag)->create([
            'title' => 'A published post',
            'body_markdown' => "## Section\n\nSome **bold** text.",
        ]);

        $response = $this->get(route('posts.show', $post));

        $response->assertOk()
            ->assertSee('A published post')
            ->assertSee('<strong>bold</strong>', escape: false)
            ->assertSee('href="'.route('tags.show', $tag).'"', escape: false)
            ->assertSee('href="'.route('projects.show', $project).'"', escape: false);
    });

    it('returns 404 for drafts and scheduled posts', function (Post $post) {
        $response = $this->get(route('posts.show', $post));

        $response->assertNotFound();
    })->with([
        'draft' => fn () => Post::factory()->draft()->create(),
        'scheduled' => fn () => Post::factory()->scheduled()->create(),
    ]);

    it('returns 404 for an unknown slug', function () {
        $response = $this->get(route('posts.show', 'no-such-post'));

        $response->assertNotFound();
    });

    it('escapes the title and excerpt', function () {
        $post = Post::factory()->create([
            'title' => '<script>alert("title")</script>',
            'excerpt' => '<img src=x onerror=alert(1)>',
        ]);

        $response = $this->get(route('posts.show', $post));

        $response->assertOk()
            ->assertDontSee('<script>alert("title")</script>', escape: false)
            ->assertDontSee('<img src=x onerror=alert(1)>', escape: false);
    });
});
