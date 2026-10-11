<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Contracts\View\View;

class TagController extends Controller
{
    /**
     * List the published posts with a tag. A tag used only by drafts 404s like an unknown one.
     */
    public function __invoke(Tag $tag): View
    {
        $posts = $tag->posts()->published()->with('tags')->get();

        abort_if($posts->isEmpty(), 404);

        return view('tags.show', [
            'tag' => $tag,
            'posts' => $posts,
        ]);
    }
}
