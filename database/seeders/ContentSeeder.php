<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use League\CommonMark\Extension\FrontMatter\Data\SymfonyYamlFrontMatterParser;
use League\CommonMark\Extension\FrontMatter\FrontMatterParser;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Loads projects and posts from Markdown files with YAML front matter (content/projects/*.md and
 * content/posts/*.md). Idempotent: records are upserted by slug, so re-running only applies changes.
 */
class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contentPath = config('blog.content_path');

        foreach ($this->markdownFiles("{$contentPath}/projects") as $file) {
            $this->seedProject($file);
        }

        foreach ($this->markdownFiles("{$contentPath}/posts") as $file) {
            $this->seedPost($file);
        }
    }

    private function seedProject(SplFileInfo $file): void
    {
        [$frontMatter, $body] = $this->parse($file);

        $this->validate($file, $frontMatter, [
            'name' => ['required', 'string'],
            'slug' => ['nullable', 'alpha_dash'],
            'tagline' => ['required', 'string'],
            'stack' => ['nullable', 'array'],
            'stack.*' => ['string'],
            'repo_url' => ['required', 'url'],
            'live_url' => ['nullable', 'url'],
            'status' => ['nullable', Rule::enum(ProjectStatus::class)],
            'featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        Project::updateOrCreate(
            ['slug' => $frontMatter['slug'] ?? $file->getFilenameWithoutExtension()],
            [
                'name' => $frontMatter['name'],
                'tagline' => $frontMatter['tagline'],
                'summary' => $body,
                'stack' => $frontMatter['stack'] ?? null,
                'repo_url' => $frontMatter['repo_url'],
                'live_url' => $frontMatter['live_url'] ?? null,
                'status' => $frontMatter['status'] ?? ProjectStatus::Active->value,
                'featured' => $frontMatter['featured'] ?? false,
                'sort_order' => $frontMatter['sort_order'] ?? 0,
            ],
        );
    }

    private function seedPost(SplFileInfo $file): void
    {
        [$frontMatter, $body] = $this->parse($file);

        $this->validate($file, $frontMatter, [
            'title' => ['required', 'string'],
            'slug' => ['nullable', 'alpha_dash'],
            'excerpt' => ['nullable', 'string'],
            'project' => ['nullable', 'string', Rule::exists(Project::class, 'slug')],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
        ]);

        // reading_minutes is computed by the ProcessPost job. The excerpt is only written when the
        // file sets one, so a generated excerpt survives re-seeding and the record stays unchanged.
        $attributes = [
            'project_id' => isset($frontMatter['project'])
                ? Project::where('slug', $frontMatter['project'])->value('id')
                : null,
            'title' => $frontMatter['title'],
            'body_markdown' => $body,
            'published_at' => $this->toDate($frontMatter['published_at'] ?? null),
        ];

        if (filled($frontMatter['excerpt'] ?? null)) {
            $attributes['excerpt'] = $frontMatter['excerpt'];
        }

        $post = Post::updateOrCreate(
            ['slug' => $frontMatter['slug'] ?? $file->getFilenameWithoutExtension()],
            $attributes,
        );

        $post->syncTagNames($frontMatter['tags'] ?? []);
    }

    /**
     * @return list<SplFileInfo>
     */
    private function markdownFiles(string $directory): array
    {
        if (! File::isDirectory($directory)) {
            return [];
        }

        return collect(File::files($directory))
            ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'md')
            ->sortBy(fn (SplFileInfo $file): string => $file->getFilename())
            ->values()
            ->all();
    }

    /**
     * @return array{0: array<string, mixed>, 1: string}
     */
    private function parse(SplFileInfo $file): array
    {
        $parsed = (new FrontMatterParser(new SymfonyYamlFrontMatterParser))->parse($file->getContents());

        $frontMatter = $parsed->getFrontMatter();

        if (! is_array($frontMatter)) {
            throw new InvalidArgumentException("{$file->getRelativePathname()}: missing YAML front matter.");
        }

        return [$frontMatter, trim($parsed->getContent())];
    }

    /**
     * @param  array<string, mixed>  $frontMatter
     * @param  array<string, mixed>  $rules
     */
    private function validate(SplFileInfo $file, array $frontMatter, array $rules): void
    {
        $validator = Validator::make($frontMatter, $rules);

        if ($validator->fails()) {
            throw new InvalidArgumentException(
                "{$file->getFilename()}: ".implode(' ', $validator->errors()->all())
            );
        }
    }

    /**
     * YAML parses unquoted dates (2026-10-11) into Unix timestamps; quoted ones stay strings.
     */
    private function toDate(mixed $value): ?Carbon
    {
        return match (true) {
            $value === null || $value === '' => null,
            is_int($value) => Carbon::createFromTimestamp($value),
            default => Carbon::parse($value),
        };
    }
}
