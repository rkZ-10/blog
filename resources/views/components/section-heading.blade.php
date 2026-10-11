@props(['title'])

<div {{ $attributes->class('mb-5 flex flex-wrap items-baseline justify-between gap-4') }}>
    <h2 class="text-2xl font-semibold">{{ $title }}</h2>

    @isset($aside)
        <div class="font-mono text-[0.78rem] text-muted">{{ $aside }}</div>
    @endisset
</div>
