<?php

use App\Models\Post;
use App\Models\Project;

describe('index', function () {
    it('lists every project in display order', function () {
        Project::factory()->create(['name' => 'Last Project', 'sort_order' => 3]);
        Project::factory()->archived()->create(['name' => 'Archived Project', 'sort_order' => 2]);
        Project::factory()->create(['name' => 'First Project', 'sort_order' => 1]);

        $response = $this->get(route('projects.index'));

        $response->assertOk()->assertSeeInOrder(['First Project', 'Archived Project', 'Archived', 'Last Project']);
    });

    it('shows each project stack on its card', function () {
        Project::factory()->create(['name' => 'Stacked Project', 'stack' => ['Go', 'DynamoDB']]);

        $response = $this->get(route('projects.index'));

        $response->assertOk()->assertSeeInOrder(['Stacked Project', 'Go', 'DynamoDB']);
    });
});

describe('show', function () {
    it('renders the project summary and links', function () {
        $project = Project::factory()->create([
            'name' => 'Shown Project',
            'summary' => 'A *rendered* summary.',
            'repo_url' => 'https://github.com/rkZ-10/shown-project',
            'live_url' => 'https://shown.example.com',
        ]);

        $response = $this->get(route('projects.show', $project));

        $response->assertOk()
            ->assertSee('Shown Project')
            ->assertSee('<em>rendered</em>', escape: false)
            ->assertSee('href="https://github.com/rkZ-10/shown-project"', escape: false)
            ->assertSee('href="https://shown.example.com"', escape: false);
    });

    it('shows the stack as chips in order', function () {
        $project = Project::factory()->create(['stack' => ['Laravel', 'MariaDB', 'Redis']]);

        $response = $this->get(route('projects.show', $project));

        $response->assertOk()
            ->assertSee('aria-label="Stack"', escape: false)
            ->assertSeeInOrder(['Laravel', 'MariaDB', 'Redis']);
    });

    it('omits the stack list when a project has no stack', function () {
        $project = Project::factory()->create(['stack' => null]);

        $response = $this->get(route('projects.show', $project));

        $response->assertOk()->assertDontSee('aria-label="Stack"', escape: false);
    });

    it('lists only its own published posts', function () {
        $project = Project::factory()->create();
        Post::factory()->for($project)->create(['title' => 'Project post']);
        Post::factory()->for($project)->draft()->create(['title' => 'Project draft']);
        Post::factory()->create(['title' => 'Unrelated post']);

        $response = $this->get(route('projects.show', $project));

        $response->assertOk()
            ->assertSee('Project post')
            ->assertDontSee('Project draft')
            ->assertDontSee('Unrelated post');
    });

    it('returns 404 for an unknown slug', function () {
        $response = $this->get(route('projects.show', 'no-such-project'));

        $response->assertNotFound();
    });
});
