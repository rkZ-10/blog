<?php

namespace App\Services;

use Illuminate\Container\Attributes\Singleton;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkProcessor;
use League\CommonMark\MarkdownConverter;

/**
 * Converts Markdown (GitHub-flavoured: tables, fenced code, task lists, autolinks) to HTML, with
 * linkable heading anchors. Rendered output is cached in the default cache store (Redis locally).
 */
#[Singleton]
class MarkdownRenderer
{
    private ?MarkdownConverter $converter = null;

    /**
     * Render Markdown to HTML without caching.
     */
    public function render(string $markdown): string
    {
        return $this->converter()->convert($markdown)->getContent();
    }

    /**
     * Render Markdown to plain text (no markup, heading anchors or entities) with whitespace collapsed,
     * for word counts and generated excerpts.
     */
    public function plainText(string $markdown): string
    {
        $html = preg_replace('/<a [^>]*class="heading-anchor"[^>]*>.*?<\/a>/s', '', $this->render($markdown));

        return Str::of(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5))->squish()->toString();
    }

    /**
     * The plain text of the body's paragraphs only (no headings, tables, lists or code), for
     * generated excerpts. Falls back to all text when there are no paragraphs.
     */
    public function proseText(string $markdown): string
    {
        preg_match_all('/<p>(.*?)<\/p>/s', $this->render($markdown), $paragraphs);

        if ($paragraphs[1] === []) {
            return $this->plainText($markdown);
        }

        return Str::of(html_entity_decode(strip_tags(implode(' ', $paragraphs[1])), ENT_QUOTES | ENT_HTML5))
            ->squish()
            ->toString();
    }

    /**
     * Render Markdown to HTML, caching the result under the given key. Callers include a version
     * (such as the model's updated_at) in the key so that edits never serve stale HTML.
     */
    public function renderCached(string $key, string $markdown): string
    {
        return Cache::remember("markdown.{$key}", now()->addWeek(), fn (): string => $this->render($markdown));
    }

    private function converter(): MarkdownConverter
    {
        if ($this->converter !== null) {
            return $this->converter;
        }

        // Content is authored by the site owner (Markdown files or the local-only admin), so raw HTML
        // such as <figure> is allowed. Unsafe link schemes (javascript:, data:) are still stripped.
        $environment = new Environment([
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
            'heading_permalink' => [
                'min_heading_level' => 2,
                'max_heading_level' => 3,
                'insert' => HeadingPermalinkProcessor::INSERT_AFTER,
                'id_prefix' => '',
                'fragment_prefix' => '',
                'apply_id_to_heading' => true,
                'html_class' => 'heading-anchor',
                'title' => 'Link to this section',
                'symbol' => '#',
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new HeadingPermalinkExtension);

        return $this->converter = new MarkdownConverter($environment);
    }
}
