<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Show the home page: intro, featured projects and latest posts.
     */
    public function __invoke(): View
    {
        return view('home', [
            'featuredProjects' => Project::featured()->ordered()->get(),
            'latestPosts' => Post::published()->with('tags')->limit(5)->get(),
        ]);
    }
}
