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

it('gives root-relative markdown images and links the base path, leaving other urls alone', function () {
    Post::factory()->create([
        'slug' => 'with-images',
        'body_markdown' => implode("\n\n", [
            '![Diagram](/images/posts/demo/diagram.png)',
            '[All projects](/projects) and [GitHub](https://github.com/rkZ-10) and [CDN](//cdn.example.com/x.js) and [section](#setup)',
        ]),
    ]);

    $response = $this->get('/blog/posts/with-images');

    $response->assertOk()
        ->assertSee('src="'.$this->origin.'/blog/images/posts/demo/diagram.png"', escape: false)
        ->assertSee('loading="lazy"', escape: false)
        ->assertSee('href="'.$this->origin.'/blog/projects"', escape: false)
        ->assertSee('href="https://github.com/rkZ-10"', escape: false)
        ->assertSee('href="//cdn.example.com/x.js"', escape: false)
        ->assertSee('href="#setup"', escape: false);
});

it('does not serve markdown html cached for a different site root', function () {
    Post::factory()->create(['slug' => 'cached-post', 'body_markdown' => '![Diagram](/images/diagram.png)']);
    config(['app.url' => $this->origin]);
    $this->get('/posts/cached-post')->assertSee('src="'.$this->origin.'/images/diagram.png"', escape: false);
    config(['app.url' => "{$this->origin}/blog"]);

    $response = $this->get('/blog/posts/cached-post');

    $response->assertOk()->assertSee('src="'.$this->origin.'/blog/images/diagram.png"', escape: false);
});

it('renders the styled 404 page for unknown paths under the base path', function () {
    $response = $this->get('/blog/no-such-page');

    $response->assertNotFound()
        ->assertSee('href="'.$this->origin.'/blog"', escape: false);
});
