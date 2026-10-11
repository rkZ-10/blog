<?php

namespace App\Models;

use App\Observers\PostObserver;
use App\Services\MarkdownRenderer;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

#[Fillable(['project_id', 'title', 'slug', 'excerpt', 'body_markdown', 'reading_minutes', 'published_at'])]
#[ObservedBy([PostObserver::class])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'reading_minutes' => 'integer',
        ];
    }

    /**
     * Resolve route model bindings by slug.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderBy('name');
    }

    /**
     * Replace the post's tags with the given names, creating tags that don't exist yet. Tags are
     * matched by slug, so "Laravel" and "laravel" are the same tag.
     *
     * @param  list<string>  $names
     */
    public function syncTagNames(array $names): void
    {
        $tagIds = collect($names)
            ->map(fn (string $name): string => trim($name))
            ->filter()
            ->map(fn (string $name): int => Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id)
            ->unique()
            ->values();

        $this->tags()->sync($tagIds);
    }

    /**
     * Posts whose publish date has passed, newest first. Drafts (null) and scheduled posts are excluded.
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at');
    }

    /**
     * Whether the post is visible on the public site.
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lte(now());
    }

    /**
     * The body rendered from Markdown to HTML. Cached per slug and update time, so editing the post
     * naturally produces a new cache key.
     */
    public function bodyHtml(): string
    {
        return app(MarkdownRenderer::class)->renderCached(
            "posts.{$this->slug}.{$this->updated_at?->timestamp}",
            $this->body_markdown,
        );
    }
}
