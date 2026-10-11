<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\View\View;

class PostController extends Controller
{
    /**
     * List every published post, newest first. No pagination: query-string pages don't survive the
     * static export.
     */
    public function index(): View
    {
        return view('posts.index', [
            'posts' => Post::published()->with('tags')->get(),
        ]);
    }

    /**
     * Show a published post. Drafts and scheduled posts 404 rather than revealing that they exist.
     */
    public function show(Post $post): View
    {
        abort_unless($post->isPublished(), 404);

        return view('posts.show', [
            'post' => $post->load(['tags', 'project']),
        ]);
    }
}
