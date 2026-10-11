<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\MarkdownRenderer;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Attributes\UniqueFor;
use Illuminate\Support\Str;

/**
 * Derives a post's reading time from its body and fills in an excerpt when the author left it empty.
 *
 * Dispatched by PostObserver after a post's content changes. Unique per post: if the post is saved
 * again while a job is still waiting, no duplicate is queued, and the pending job re-reads the latest
 * row when it runs (SerializesModels stores only the id).
 */
#[DeleteWhenMissingModels]
#[Tries(3)]
#[Backoff([5, 30])]
#[UniqueFor(600)]
class ProcessPost implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Average adult silent-reading speed for technical prose.
     */
    public const int WORDS_PER_MINUTE = 200;

    public const int EXCERPT_LENGTH = 160;

    /**
     * Create a new job instance.
     */
    public function __construct(public Post $post) {}

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return (string) $this->post->id;
    }

    /**
     * Execute the job.
     */
    public function handle(MarkdownRenderer $markdown): void
    {
        $plainText = $markdown->plainText($this->post->body_markdown);

        $this->post->reading_minutes = max(1, (int) ceil(str_word_count($plainText) / self::WORDS_PER_MINUTE));

        if (blank($this->post->excerpt)) {
            $this->post->excerpt = Str::limit(
                $markdown->proseText($this->post->body_markdown),
                self::EXCERPT_LENGTH,
                '…',
                preserveWords: true,
            );
        }

        // Quietly, so the observer doesn't dispatch this job again in response to its own write.
        $this->post->saveQuietly();
    }
}
