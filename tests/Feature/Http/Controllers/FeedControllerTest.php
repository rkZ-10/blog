<?php

use App\Models\Post;

it('returns an rss feed of published posts with absolute links', function () {
    $post = Post::factory()->create([
        'title' => 'Feed post',
        'body_markdown' => 'Feed **body**.',
        'published_at' => '2026-03-04 05:06:07',
    ]);
    Post::factory()->draft()->create(['title' => 'Feed draft']);

    $response = $this->get(route('feed'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
        ->assertSee('<title>Feed post</title>', escape: false)
        ->assertSee('<link>'.route('posts.show', $post).'</link>', escape: false)
        ->assertSee('<pubDate>Wed, 04 Mar 2026 05:06:07 +0000</pubDate>', escape: false)
        ->assertSee('&lt;strong&gt;body&lt;/strong&gt;', escape: false)
        ->assertDontSee('Feed draft');
});

it('returns well-formed xml when titles contain markup characters', function () {
    Post::factory()->create(['title' => 'Ampersands & <angle brackets>']);

    $response = $this->get(route('feed'));

    expect(simplexml_load_string($response->getContent()))->not->toBeFalse();
});
