<?php

use App\Jobs\ProcessPost;
use App\Models\Post;
use App\Services\MarkdownRenderer;
use Illuminate\Support\Facades\Queue;

function runProcessPost(Post $post): Post
{
    (new ProcessPost($post))->handle(app(MarkdownRenderer::class));

    return $post->refresh();
}

it('computes reading minutes at 200 words per minute, rounding up', function (int $words, int $expectedMinutes) {
    $post = Post::factory()->createQuietly([
        'body_markdown' => str_repeat('word ', $words),
        'reading_minutes' => null,
    ]);

    expect(runProcessPost($post)->reading_minutes)->toBe($expectedMinutes);
})->with([
    'short post' => [20, 1],
    'exactly two minutes' => [400, 2],
    'just over two minutes' => [401, 3],
]);

it('builds the excerpt from paragraph text, skipping headings, tables and code', function () {
    $post = Post::factory()->createQuietly([
        'body_markdown' => "## Intro\n\nThe *quick* fox & friends.\n\n| A | B |\n| --- | --- |\n| 1 | 2 |\n\n```php\necho 'hi';\n```\n\nSecond paragraph.",
        'excerpt' => null,
    ]);

    expect(runProcessPost($post)->excerpt)->toBe('The quick fox & friends. Second paragraph.');
});

it('falls back to all text for an excerpt when the body has no paragraphs', function () {
    $post = Post::factory()->createQuietly([
        'body_markdown' => "- first item\n- second item",
        'excerpt' => null,
    ]);

    expect(runProcessPost($post)->excerpt)->toBe('first item second item');
});

it('fills an empty excerpt with the first 160 characters, ending on a whole word', function () {
    $post = Post::factory()->createQuietly([
        'body_markdown' => str_repeat('alpha ', 40),
        'excerpt' => null,
    ]);

    expect(runProcessPost($post)->excerpt)->toBe(implode(' ', array_fill(0, 26, 'alpha')).'…');
});

it('keeps an excerpt the author wrote', function () {
    $post = Post::factory()->createQuietly([
        'body_markdown' => 'A body that would make a different excerpt.',
        'excerpt' => 'Hand-written excerpt.',
    ]);

    expect(runProcessPost($post)->excerpt)->toBe('Hand-written excerpt.');
});

it('does not dispatch itself again when it saves the post', function () {
    Queue::fake();
    $post = Post::factory()->createQuietly(['excerpt' => null]);

    runProcessPost($post);

    Queue::assertNothingPushed();
});

it('is unique per post', function () {
    $post = Post::factory()->createQuietly();

    expect((new ProcessPost($post))->uniqueId())->toBe((string) $post->id);
});
