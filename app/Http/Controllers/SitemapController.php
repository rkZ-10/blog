<?php

namespace App\Http\Controllers;

use App\Services\PublicPages;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * An XML sitemap of every public page: static pages, published posts, projects and tags that
     * have at least one published post.
     */
    public function __invoke(PublicPages $pages): Response
    {
        return response()
            ->view('sitemap', [
                'posts' => $pages->posts(),
                'projects' => $pages->projects(),
                'tags' => $pages->tags(),
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
