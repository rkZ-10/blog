<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * Local-only CRUD for posts, for practice. It edits the database only; the Markdown files in
 * content/posts stay the source of truth and re-seeding overwrites matching slugs.
 */
class PostController extends Controller
{
    /**
     * List every post, drafts included, most recently edited first.
     */
    public function index(): View
    {
        return view('admin.posts.index', [
            'posts' => Post::with('project')->latest('updated_at')->get(),
        ]);
    }

    /**
     * Show the form for creating a post.
     */
    public function create(): View
    {
        return view('admin.posts.create', [
            'post' => new Post,
            'projects' => Project::ordered()->get(),
        ]);
    }

    /**
     * Store a new post with its tags.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = DB::transaction(function () use ($request): Post {
            $post = Post::create($request->safe()->except('tags'));
            $post->syncTagNames($request->tagNames());

            return $post;
        });

        return to_route('admin.posts.edit', $post)->with('status', 'Post created.');
    }

    /**
     * Show the form for editing a post.
     */
    public function edit(Post $post): View
    {
        return view('admin.posts.edit', [
            'post' => $post->load('tags'),
            'projects' => Project::ordered()->get(),
        ]);
    }

    /**
     * Update a post and its tags.
     */
    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        DB::transaction(function () use ($request, $post): void {
            $post->update($request->safe()->except('tags'));
            $post->syncTagNames($request->tagNames());
        });

        return to_route('admin.posts.edit', $post)->with('status', 'Post saved.');
    }

    /**
     * Delete a post. Its tag links go with it (cascade); the tags themselves stay.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return to_route('admin.posts.index')->with('status', "Deleted \"{$post->title}\".");
    }
}
