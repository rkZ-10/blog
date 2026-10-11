<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * An XML sitemap of every public page: static pages, published posts, projects and tags that
     * have at least one published post.
     */
    public function __invoke(): Response
    {
        return response()
            ->view('sitemap', [
                'posts' => Post::published()->get(),
                'projects' => Project::ordered()->get(),
                'tags' => Tag::whereHas('posts', fn (Builder $query) => $query->published())->orderBy('slug')->get(),
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
