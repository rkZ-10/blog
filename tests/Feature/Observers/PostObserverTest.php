<?php

use App\Jobs\ProcessPost;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

it('queues processing when a post is created', function () {
    $post = Post::factory()->create();

    Queue::assertPushed(ProcessPost::class, fn (ProcessPost $job) => $job->post->is($post));
});

it('queues processing when the body or excerpt changes', function (array $changes) {
    $post = Post::factory()->createQuietly();

    $post->update($changes);

    Queue::assertPushed(ProcessPost::class, 1);
})->with([
    'body' => [['body_markdown' => 'A new body.']],
    'excerpt cleared' => [['excerpt' => null]],
]);

it('does not queue processing when only the title or publish date changes', function () {
    $post = Post::factory()->createQuietly();

    $post->update(['title' => 'A new title', 'published_at' => now()->subHour()]);

    Queue::assertNothingPushed();
});

it('does not queue processing again when a just-created post has its title changed', function () {
    $post = Post::factory()->create();

    $post->update(['title' => 'Renamed straight away']);

    Queue::assertPushed(ProcessPost::class, 1);
});

it('runs the job only once the surrounding transaction commits', function (bool $commits, int $expectedRuns) {
    // Queue::fake() ignores afterCommit(), so use the real (sync) queue and count processed jobs.
    $this->app->forgetInstance('queue');
    Queue::clearResolvedInstances();
    $runs = 0;
    Queue::before(function () use (&$runs) {
        $runs++;
    });

    rescue(fn () => DB::transaction(function () use ($commits) {
        Post::factory()->create();

        if (! $commits) {
            throw new RuntimeException('Roll back.');
        }
    }), report: false);

    expect($runs)->toBe($expectedRuns);
})->with([
    'committed' => [true, 1],
    'rolled back' => [false, 0],
]);
