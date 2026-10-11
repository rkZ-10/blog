@props(['href' => null])

@php
    $classes = 'block rounded-[14px] border border-border bg-surface px-[26px] py-7 text-fg no-underline';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class([$classes, 'transition-colors hover:border-accent']) }}>
        {{ $slot }}
    </a>
@else
    <div {{ $attributes->class($classes) }}>
        {{ $slot }}
    </div>
@endif
