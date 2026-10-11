<?php

use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;

it('lists public pages, published posts, projects and tags in use', function () {
    $project = Project::factory()->create();
    $usedTag = Tag::factory()->create();
    $draftOnlyTag = Tag::factory()->create();
    $post = Post::factory()->hasAttached($usedTag)->create();
    $draft = Post::factory()->draft()->hasAttached($draftOnlyTag)->create();

    $response = $this->get(route('sitemap'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee('<loc>'.route('home').'</loc>', escape: false)
        ->assertSee('<loc>'.route('posts.show', $post).'</loc>', escape: false)
        ->assertSee('<loc>'.route('projects.show', $project).'</loc>', escape: false)
        ->assertSee('<loc>'.route('tags.show', $usedTag).'</loc>', escape: false)
        ->assertDontSee(route('posts.show', $draft), escape: false)
        ->assertDontSee(route('tags.show', $draftOnlyTag), escape: false);

    expect(simplexml_load_string($response->getContent()))->not->toBeFalse();
});
