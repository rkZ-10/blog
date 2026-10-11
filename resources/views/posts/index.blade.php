<x-layouts.app title="Blog">
    <section class="mx-auto max-w-text">
        <x-eyebrow>Blog</x-eyebrow>
        <h1 class="text-[2.2rem] font-semibold leading-tight">Posts</h1>
        <p class="mt-3 text-muted">
            Write-ups of things I've built. Also available as an <a href="{{ route('feed') }}">RSS feed</a>.
        </p>

        <x-post-list :posts="$posts" class="mt-10" />
    </section>
</x-layouts.app>
