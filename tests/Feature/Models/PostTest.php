<?php

use App\Models\Post;

it('scopes published posts to past publish dates, newest first', function () {
    $older = Post::factory()->create(['published_at' => now()->subWeek()]);
    $newer = Post::factory()->create(['published_at' => now()->subDay()]);
    Post::factory()->draft()->create();
    Post::factory()->scheduled()->create();

    $published = Post::published()->pluck('id');

    expect($published->all())->toBe([$newer->id, $older->id]);
});

it('treats a post as published from its publish time onwards', function () {
    $this->freezeTime();
    $post = Post::factory()->create(['published_at' => now()->addMinute()]);

    expect($post->isPublished())->toBeFalse();

    $this->travel(1)->minutes();

    expect($post->isPublished())->toBeTrue();
});

it('serves cached body html until the post is updated', function () {
    $post = Post::factory()->create(['body_markdown' => 'First **version**']);
    $post->bodyHtml();

    Post::whereKey($post->id)->toBase()->update(['body_markdown' => 'Changed without touching updated_at']);

    expect($post->refresh()->bodyHtml())->toContain('First <strong>version</strong>');

    $this->travel(1)->seconds();
    $post->update(['body_markdown' => 'Second **version**']);

    expect($post->bodyHtml())->toContain('Second <strong>version</strong>');
});
