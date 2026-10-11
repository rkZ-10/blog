<x-layouts.app :title="'#'.$tag->slug" :description="'Posts tagged '.$tag->name.'.'">
    <section class="mx-auto max-w-text">
        <x-eyebrow>Tag</x-eyebrow>
        <h1 class="text-[2.2rem] font-semibold leading-tight">#{{ $tag->slug }}</h1>
        <p class="mt-3 text-muted">{{ trans_choice('{1} :count post|[2,*] :count posts', $posts->count()) }} tagged {{ $tag->name }}.</p>

        <x-post-list :posts="$posts" class="mt-10" />

        <p class="mt-10 text-sm"><a href="{{ route('posts.index') }}">← All posts</a></p>
    </section>
</x-layouts.app>
