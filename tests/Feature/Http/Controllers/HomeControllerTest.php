<?php

use App\Models\Post;
use App\Models\Project;

it('shows featured projects in display order and hides the rest', function () {
    Project::factory()->featured()->create(['name' => 'Second Featured', 'sort_order' => 2]);
    Project::factory()->featured()->create(['name' => 'First Featured', 'sort_order' => 1]);
    Project::factory()->create(['name' => 'Not Featured']);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSeeInOrder(['First Featured', 'Second Featured'])
        ->assertDontSee('Not Featured');
});

it('shows the five latest published posts and no drafts', function () {
    Post::factory()->count(6)->sequence(fn ($sequence) => [
        'title' => "Published post {$sequence->index}",
        'published_at' => now()->subDays($sequence->index + 1),
    ])->create();
    Post::factory()->draft()->create(['title' => 'Draft post']);
    Post::factory()->scheduled()->create(['title' => 'Scheduled post']);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSeeInOrder(['Published post 0', 'Published post 4'])
        ->assertDontSee('Published post 5')
        ->assertDontSee('Draft post')
        ->assertDontSee('Scheduled post');
});

it('renders empty states when there is no content', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Projects are on their way.')
        ->assertSee('No posts yet.');
});
