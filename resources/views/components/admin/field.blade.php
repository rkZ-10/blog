@props(['name', 'label', 'hint' => null])

<div {{ $attributes->class('flex flex-col gap-1.5') }}>
    <label for="{{ $name }}" class="font-mono text-[0.72rem] uppercase tracking-[0.08em] text-muted">{{ $label }}</label>
    {{ $slot }}
    @if ($hint)
        <p class="text-xs text-muted">{{ $hint }}</p>
    @endif
    @error($name)
        <p id="{{ $name }}-error" class="text-sm text-danger">{{ $message }}</p>
    @enderror
</div>
