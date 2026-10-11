@php
    $inputClasses = 'w-full rounded-md border border-border bg-surface px-3 py-2 text-fg focus:border-accent focus:outline-none';
@endphp

@csrf

<div class="flex flex-col gap-6">
    <x-admin.field name="title" label="Title">
        <input id="title" name="title" type="text" value="{{ old('title', $post->title) }}" required
            @error('title') aria-invalid="true" aria-describedby="title-error" @enderror
            class="{{ $inputClasses }}">
    </x-admin.field>

    <x-admin.field name="slug" label="Slug" hint="Leave empty to derive it from the title.">
        <input id="slug" name="slug" type="text" value="{{ old('slug', $post->slug) }}"
            @error('slug') aria-invalid="true" aria-describedby="slug-error" @enderror
            class="{{ $inputClasses }} font-mono text-sm">
    </x-admin.field>

    <x-admin.field name="excerpt" label="Excerpt" hint="Optional. When empty, a queued job fills it from the body.">
        <textarea id="excerpt" name="excerpt" rows="2"
            @error('excerpt') aria-invalid="true" aria-describedby="excerpt-error" @enderror
            class="{{ $inputClasses }}">{{ old('excerpt', $post->excerpt) }}</textarea>
    </x-admin.field>

    <x-admin.field name="body_markdown" label="Body (Markdown)">
        <textarea id="body_markdown" name="body_markdown" rows="18" required
            @error('body_markdown') aria-invalid="true" aria-describedby="body_markdown-error" @enderror
            class="{{ $inputClasses }} font-mono text-sm leading-relaxed">{{ old('body_markdown', $post->body_markdown) }}</textarea>
    </x-admin.field>

    <div class="grid gap-6 sm:grid-cols-2">
        <x-admin.field name="project_id" label="Project">
            <select id="project_id" name="project_id" class="{{ $inputClasses }}">
                <option value="">None</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" @selected((string) old('project_id', $post->project_id) === (string) $project->id)>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </x-admin.field>

        <x-admin.field name="published_at" label="Publish date" hint="Empty keeps it a draft; a future date schedules it.">
            <input id="published_at" name="published_at" type="datetime-local"
                value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}"
                @error('published_at') aria-invalid="true" aria-describedby="published_at-error" @enderror
                class="{{ $inputClasses }}">
        </x-admin.field>
    </div>

    <x-admin.field name="tags" label="Tags" hint="Comma-separated, e.g. Laravel, Redis.">
        <input id="tags" name="tags" type="text" value="{{ old('tags', $post->tags->pluck('name')->implode(', ')) }}"
            @error('tags') aria-invalid="true" aria-describedby="tags-error" @enderror
            class="{{ $inputClasses }}">
    </x-admin.field>

    <div class="flex flex-wrap items-center gap-4">
        <button type="submit" class="cursor-pointer rounded-md bg-accent px-4 py-2 text-sm font-medium text-bg hover:opacity-90">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('admin.posts.index') }}" class="text-sm">Cancel</a>
    </div>
</div>
