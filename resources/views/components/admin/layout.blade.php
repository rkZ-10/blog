@props(['title'])

<x-layouts.app :title="$title.' · Admin'">
    <x-slot:head>
        <meta name="robots" content="noindex, nofollow">
    </x-slot:head>

    <section class="mx-auto max-w-text">
        <x-eyebrow>Admin · local only</x-eyebrow>
        <h1 class="text-[2.2rem] font-semibold leading-tight">{{ $title }}</h1>

        @if (session('status'))
            <p role="status" class="mt-5 rounded-md border border-accent bg-accent-soft px-4 py-2.5 text-sm text-fg">
                {{ session('status') }}
            </p>
        @endif

        <div class="mt-8">
            {{ $slot }}
        </div>
    </section>
</x-layouts.app>
