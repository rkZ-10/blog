@props(['href' => null, 'accent' => false])

@php
    $classes = $accent
        ? 'rounded-full bg-accent-soft px-2.5 py-0.5 font-mono text-[0.72rem] text-accent no-underline'
        : 'rounded-md border border-border bg-chip px-2 py-0.5 font-mono text-[0.76rem] text-fg no-underline';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class([$classes, 'hover:border-accent hover:text-accent']) }}>{{ $slot }}</a>
@else
    <span {{ $attributes->class($classes) }}>{{ $slot }}</span>
@endif
