@props(['stack'])

@if (! empty($stack))
    <ul {{ $attributes->class('flex flex-wrap gap-1.5') }} aria-label="Stack">
        @foreach ($stack as $technology)
            <li><x-chip>{{ $technology }}</x-chip></li>
        @endforeach
    </ul>
@endif
