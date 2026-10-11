<x-layouts.app :title="$post->title" :description="$post->excerpt">
    <article class="mx-auto max-w-text">
        <header>
            <x-post-meta :post="$post" class="mb-2.5" />
            <h1 class="text-[clamp(1.9rem,4.5vw,2.6rem)] font-semibold leading-[1.12]">{{ $post->title }}</h1>
            @if ($post->excerpt)
                <p class="mt-4 text-[1.08rem] text-muted">{{ $post->excerpt }}</p>
            @endif
            <x-tag-list :tags="$post->tags" class="mt-4" />
        </header>

        <div class="prose mt-10">
            {!! $post->bodyHtml() !!}
        </div>

        @if ($post->project)
            <aside class="mt-14">
                <x-card :href="route('projects.show', $post->project)">
                    <x-eyebrow>Project</x-eyebrow>
                    <p class="font-serif text-[1.2rem] font-semibold">{{ $post->project->name }} →</p>
                    <p class="mt-1 text-[0.94rem] text-muted">{{ $post->project->tagline }}</p>
                </x-card>
            </aside>
        @endif

        <p class="mt-10 text-sm"><a href="{{ route('posts.index') }}">← All posts</a></p>
    </article>
</x-layouts.app>
