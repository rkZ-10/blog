<x-layouts.app title="Projects">
    <section class="mx-auto max-w-text">
        <x-eyebrow>Projects</x-eyebrow>
        <h1 class="text-[2.2rem] font-semibold leading-tight">Projects</h1>
        <p class="mt-3 text-muted">Things I've built outside work, with the write-ups that go with them.</p>

        @if ($projects->isEmpty())
            <p class="mt-10 text-muted">Projects are on their way.</p>
        @else
            <div class="mt-10 grid gap-5 md:grid-cols-2">
                @foreach ($projects as $project)
                    <x-project-card :project="$project" />
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.app>
