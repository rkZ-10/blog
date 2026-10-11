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

        @if ($featuredProjects->isEmpty())
            <p class="text-muted">Projects are on their way.</p>
        @else
            <div class="grid gap-5 md:grid-cols-2">
                @foreach ($featuredProjects as $project)
                    <x-project-card :project="$project" />
                @endforeach
            </div>
        @endif
    </section>

    <section class="mx-auto max-w-text border-t border-border py-9">
        <x-section-heading title="Latest posts">
            <x-slot:aside>
                <a href="{{ route('posts.index') }}" class="text-muted hover:text-accent">All posts →</a>
            </x-slot:aside>
        </x-section-heading>

        <x-post-list :posts="$latestPosts" />
    </section>
</x-layouts.app>
