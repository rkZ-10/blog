<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    /**
     * The RSS 2.0 feed of the latest published posts.
     */
    public function __invoke(): Response
    {
        $posts = Post::published()->limit(20)->get();

        return response()
            ->view('feed', ['posts' => $posts])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
