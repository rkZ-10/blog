<?php

use App\Models\Post;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

it('returns 404 for admin pages outside the local environment', function (string $environment) {
    $this->app['env'] = $environment;
    $post = Post::factory()->create();

    $this->get(route('admin.index'))->assertNotFound();
    $this->get(route('admin.posts.index'))->assertNotFound();
    $this->get(route('admin.posts.create'))->assertNotFound();
    $this->get(route('admin.posts.edit', $post))->assertNotFound();
})->with(['production', 'testing']);

it('rejects admin writes outside the local environment without changing anything', function () {
    $this->app['env'] = 'production';
    // Isolate this middleware: outside "testing" the CSRF check would answer 419 first.
    $this->withoutMiddleware(PreventRequestForgery::class);
    $post = Post::factory()->create(['title' => 'Untouched']);

    $this->post(route('admin.posts.store'), ['title' => 'New', 'body_markdown' => 'Body'])->assertNotFound();
    $this->put(route('admin.posts.update', $post), ['title' => 'Changed', 'body_markdown' => 'Body'])->assertNotFound();
    $this->delete(route('admin.posts.destroy', $post))->assertNotFound();

    expect(Post::count())->toBe(1);
    expect($post->refresh()->title)->toBe('Untouched');
});

it('allows admin pages in the local environment', function () {
    $this->app['env'] = 'local';

    $response = $this->get(route('admin.posts.index'));

    $response->assertOk();
});
