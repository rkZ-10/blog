<?php

use App\Models\Post;
use App\Models\Tag;

it('lists only published posts with the tag', function () {
    $tag = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    Post::factory()->hasAttached($tag)->create(['title' => 'Tagged post']);
    Post::factory()->draft()->hasAttached($tag)->create(['title' => 'Tagged draft']);
    Post::factory()->create(['title' => 'Untagged post']);

    $response = $this->get(route('tags.show', $tag));

    $response->assertOk()
        ->assertSee('#laravel')
        ->assertSee('Tagged post')
        ->assertDontSee('Tagged draft')
        ->assertDontSee('Untagged post');
});

it('returns 404 when the tag only has drafts', function () {
    $tag = Tag::factory()->create();
    Post::factory()->draft()->hasAttached($tag)->create();

    $response = $this->get(route('tags.show', $tag));

    $response->assertNotFound();
});

it('returns 404 for an unknown tag', function () {
    $response = $this->get(route('tags.show', 'no-such-tag'));

    $response->assertNotFound();
});
