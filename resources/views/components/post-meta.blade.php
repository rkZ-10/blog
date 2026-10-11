@props(['post'])

<p {{ $attributes->class('font-mono text-[0.76rem] text-muted') }}>
    <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j M Y') }}</time>
    @if ($post->reading_minutes)
        <span aria-hidden="true">·</span> {{ $post->reading_minutes }} min read
    @endif
</p>
