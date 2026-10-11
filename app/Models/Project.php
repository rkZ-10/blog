<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Services\MarkdownRenderer;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'tagline', 'summary', 'stack', 'repo_url', 'live_url', 'status', 'featured', 'sort_order'])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'stack' => 'array',
            'featured' => 'boolean',
            'sort_order' => 'integer',
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
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Only projects flagged for the home page.
     */
    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('featured', true);
    }

    /**
     * The curated display order, falling back to name.
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * The summary rendered from Markdown to HTML (cached).
     */
    public function summaryHtml(): string
    {
        return app(MarkdownRenderer::class)->renderCached(
            "projects.{$this->slug}.{$this->updated_at?->timestamp}",
            $this->summary,
        );
    }
}
