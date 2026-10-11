<x-layouts.app title="Page not found">
    <section class="mx-auto max-w-text">
        <x-eyebrow>404</x-eyebrow>
        <h1 class="text-[2.2rem] font-semibold leading-tight">This page doesn't exist</h1>
        <p class="mt-4">
            The link may be old, or the post may have moved. Try the
            <a href="{{ route('posts.index') }}">blog</a> or go back to the
            <a href="{{ route('home') }}">home page</a>.
        </p>
    </section>
</x-layouts.app>
