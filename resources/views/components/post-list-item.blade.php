@props(['post'])

<li {{ $attributes->class('py-5 first:pt-0') }}>
    <x-post-meta :post="$post" />
    <h3 class="mt-1 text-xl font-semibold">
        <a href="{{ route('posts.show', $post) }}" class="text-fg no-underline hover:text-accent">{{ $post->title }}</a>
    </h3>
    @if ($post->excerpt)
        <p class="mt-2 text-[0.94rem]">{{ $post->excerpt }}</p>
    @endif
    <x-tag-list :tags="$post->tags" class="mt-3" />
</li>
