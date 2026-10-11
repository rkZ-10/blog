@props(['tags'])

@if ($tags->isNotEmpty())
    <ul {{ $attributes->class('flex flex-wrap gap-1.5') }} aria-label="Tags">
        @foreach ($tags as $tag)
            <li><x-chip :href="route('tags.show', $tag)">#{{ $tag->slug }}</x-chip></li>
        @endforeach
    </ul>
@endif
