<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Contracts\View\View;

class ProjectController extends Controller
{
    /**
     * List every project in display order.
     */
    public function index(): View
    {
        return view('projects.index', [
            'projects' => Project::ordered()->get(),
        ]);
    }

    /**
     * Show a project with its published posts.
     */
    public function show(Project $project): View
    {
        return view('projects.show', [
            'project' => $project,
            'posts' => $project->posts()->published()->with('tags')->get(),
        ]);
    }
}
