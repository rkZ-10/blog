<x-layouts.app :title="$project->name" :description="$project->tagline">
    <article class="mx-auto max-w-text">
        <header>
            <x-eyebrow>Project · {{ $project->status->label() }}</x-eyebrow>
            <h1 class="text-[clamp(1.9rem,4.5vw,2.6rem)] font-semibold leading-[1.12]">{{ $project->name }}</h1>
            <p class="mt-4 text-[1.08rem] text-muted">{{ $project->tagline }}</p>
            <p class="mt-4 flex flex-wrap gap-x-5 gap-y-1 text-sm">
                <a href="{{ $project->repo_url }}">Source on GitHub ↗</a>
                @if ($project->live_url)
                    <a href="{{ $project->live_url }}">Live site ↗</a>
                @endif
            </p>
            <x-stack-list :stack="$project->stack" class="mt-4" />
        </header>

        <div class="prose mt-10">
            {!! $project->summaryHtml() !!}
        </div>

        <section class="mt-14 border-t border-border pt-9">
            <x-section-heading title="Related posts" />
            <x-post-list :posts="$posts" empty="No posts about this project yet." />
        </section>

        <p class="mt-10 text-sm"><a href="{{ route('projects.index') }}">← All projects</a></p>
    </article>
</x-layouts.app>
