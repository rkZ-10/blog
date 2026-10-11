<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Show the home page: intro, featured projects and latest posts.
     *
     * The data is placeholder content until the Post and Project models land in Phase 3.
     */
    public function __invoke(): View
    {
        return view('home', [
            'featuredProjects' => $this->placeholderProjects(),
            'latestPosts' => $this->placeholderPosts(),
        ]);
    }

    /**
     * @return list<array{name: string, tagline: string, status: string, stack: list<string>, repoUrl: string}>
     */
    private function placeholderProjects(): array
    {
        return [
            [
                'name' => 'Travel Assistant',
                'tagline' => 'An AI agent for Indian domestic flights: live fares, cited fare rules and a 23-case eval suite.',
                'status' => 'Personal project',
                'stack' => ['MCP', 'Hybrid RAG', 'Claude Agent SDK', 'FastAPI', 'React'],
                'repoUrl' => 'https://github.com/rkZ-10/travel-assistant',
            ],
            [
                'name' => 'This blog',
                'tagline' => 'A Laravel 13 app with MariaDB, Redis and queued jobs, exported to static HTML for GitHub Pages.',
                'status' => 'In progress',
                'stack' => ['Laravel', 'MariaDB', 'Redis', 'Pest'],
                'repoUrl' => 'https://github.com/rkZ-10/blog',
            ],
        ];
    }

    /**
     * @return list<array{title: string, excerpt: string, tags: list<string>}>
     */
    private function placeholderPosts(): array
    {
        return [
            [
                'title' => 'Building a travel assistant you can measure',
                'excerpt' => 'How an MCP server, hybrid retrieval and an eval suite kept an LLM agent from inventing fees, and the bugs the evals caught.',
                'tags' => ['ai', 'mcp', 'evals'],
            ],
            [
                'title' => 'Building this site with Laravel',
                'excerpt' => 'A static export of a real Laravel app: MariaDB, a Redis cache, a queued job, tests, and what Scout would add on a live server.',
                'tags' => ['laravel', 'php'],
            ],
        ];
    }
}
