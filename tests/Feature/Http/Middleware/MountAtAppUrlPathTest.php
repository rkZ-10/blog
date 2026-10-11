<?php

use App\Models\Post;

beforeEach(function () {
    $this->origin = rtrim(url('/'), '/');
    config(['app.url' => "{$this->origin}/blog"]);
});

it('serves pages under the APP_URL path with links that keep the prefix', function () {
    $post = Post::factory()->create(['slug' => 'mounted-post']);

    $response = $this->get('/blog/posts');

    $response->assertOk()
        ->assertSee('href="'.$this->origin.'/blog/posts/mounted-post"', escape: false)
        ->assertSee('href="'.$this->origin.'/blog/projects"', escape: false);
});

it('serves the home page at the bare base path', function () {
    $response = $this->get('/blog');

    $response->assertOk()
        ->assertSee('Notes on backend systems and AI tooling')
        ->assertSee('<link rel="canonical" href="'.$this->origin.'/blog">', escape: false);
});

it('renders the styled 404 page for unknown paths under the base path', function () {
    $response = $this->get('/blog/no-such-page');

    $response->assertNotFound()
        ->assertSee('href="'.$this->origin.'/blog"', escape: false);
});
