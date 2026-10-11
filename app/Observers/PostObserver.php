<?php

namespace App\Observers;

use App\Jobs\ProcessPost;
use App\Models\Post;

/**
 * Queues ProcessPost whenever a post's content changes. Dispatching after commit means a worker never
 * picks up a post that a surrounding transaction later rolls back.
 */
class PostObserver
{
    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post): void
    {
        ProcessPost::dispatch($post)->afterCommit();
    }

    /**
     * Handle the Post "updated" event. Only content changes need reprocessing; editing a title or
     * publish date does not.
     */
    public function updated(Post $post): void
    {
        if ($post->wasChanged(['body_markdown', 'excerpt'])) {
            ProcessPost::dispatch($post)->afterCommit();
        }
    }
}
