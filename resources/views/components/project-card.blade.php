@props(['project'])

<x-card :href="route('projects.show', $project)" {{ $attributes->class('flex flex-col gap-3') }}>
    <div class="flex flex-wrap items-baseline justify-between gap-3">
        <h3 class="text-[1.3rem] font-semibold">{{ $project->name }}</h3>
        <x-chip accent>{{ $project->status->label() }}</x-chip>
    </div>
    <p class="text-[0.94rem] text-muted">{{ $project->tagline }}</p>
    <x-stack-list :stack="$project->stack" class="mt-auto pt-1" />
</x-card>
