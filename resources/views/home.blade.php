<x-layouts.app>
    <section class="reveal mx-auto max-w-text pb-11">
        <x-eyebrow>Blog &amp; projects</x-eyebrow>
        <h1 class="text-[clamp(2rem,5vw,2.9rem)] font-semibold leading-[1.08]">
            Notes on backend systems and AI tooling
        </h1>
        <p class="mt-5 max-w-[62ch] text-[1.08rem]">
            I'm Arkesh, a lead backend engineer working on distributed systems on AWS. This is where I write up
            the things I build outside work: what they do, how they're put together, and the problems that
            took longest to fix.
        </p>
        <p class="mt-3 text-sm text-muted">
            Looking for my experience and case studies? They're on the
            <a href="https://rkz-10.github.io/">portfolio ↗</a>.
        </p>
    </section>

    <section class="mx-auto max-w-text border-t border-border py-9">
        <x-section-heading title="Featured projects">
            <x-slot:aside>
                <a href="{{ route('projects.index') }}" class="text-muted hover:text-accent">All projects →</a>
            </x-slot:aside>
        </x-section-heading>

        <div class="grid gap-5 md:grid-cols-2">
            @foreach ($featuredProjects as $project)
                <x-card :href="$project['repoUrl']" class="flex flex-col gap-3">
                    <div class="flex flex-wrap items-baseline justify-between gap-3">
                        <h3 class="text-[1.3rem] font-semibold">{{ $project['name'] }}</h3>
                        <x-chip accent>{{ $project['status'] }}</x-chip>
                    </div>
                    <p class="text-[0.94rem] text-muted">{{ $project['tagline'] }}</p>
                    <div class="mt-auto flex flex-wrap gap-1.5 pt-1">
                        @foreach ($project['stack'] as $technology)
                            <x-chip>{{ $technology }}</x-chip>
                        @endforeach
                    </div>
                </x-card>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-text border-t border-border py-9">
        <x-section-heading title="Latest posts">
            <x-slot:aside>
                <a href="{{ route('posts.index') }}" class="text-muted hover:text-accent">All posts →</a>
            </x-slot:aside>
        </x-section-heading>

        <ul class="divide-y divide-border">
            @foreach ($latestPosts as $post)
                <li class="py-5 first:pt-0">
                    <p class="font-mono text-[0.76rem] text-muted">Coming soon</p>
                    <h3 class="mt-1 text-xl font-semibold">{{ $post['title'] }}</h3>
                    <p class="mt-2 text-[0.94rem]">{{ $post['excerpt'] }}</p>
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach ($post['tags'] as $tag)
                            <x-chip>#{{ $tag }}</x-chip>
                        @endforeach
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
</x-layouts.app>
