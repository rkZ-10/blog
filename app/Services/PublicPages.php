<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * The single list of what's public: used by the sitemap and by the static export, so the two can't
 * drift apart. Admin pages and the health check are deliberately absent.
 */
class PublicPages
{
    /**
     * @return Collection<int, Post>
     */
    public function posts(): Collection
    {
        return Post::published()->get();
    }

    /**
     * @return Collection<int, Project>
     */
    public function projects(): Collection
    {
        return Project::ordered()->get();
    }

    /**
     * Tags that have at least one published post (a tag used only by drafts 404s).
     *
     * @return Collection<int, Tag>
     */
    public function tags(): Collection
    {
        return Tag::whereHas('posts', fn (Builder $query) => $query->published())->orderBy('slug')->get();
    }

    /**
     * Every URL the static export writes to disk.
     *
     * @return list<string>
     */
    public function exportUrls(): array
    {
        return [
            route('home'),
            route('posts.index'),
            route('projects.index'),
            route('feed'),
            route('sitemap'),
            route('not-found'),
            ...$this->posts()->map(fn (Post $post): string => route('posts.show', $post)),
            ...$this->projects()->map(fn (Project $project): string => route('projects.show', $project)),
            ...$this->tags()->map(fn (Tag $tag): string => route('tags.show', $tag)),
        ];
    }
}
