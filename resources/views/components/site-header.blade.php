@php
    $links = [
        ['label' => 'Blog', 'href' => route('posts.index'), 'active' => request()->routeIs('posts.*', 'tags.*')],
        ['label' => 'Projects', 'href' => route('projects.index'), 'active' => request()->routeIs('projects.*')],
        ['label' => 'Portfolio ↗', 'href' => 'https://rkz-10.github.io/', 'active' => false],
        ['label' => 'Résumé', 'href' => asset('resume.pdf'), 'active' => false],
    ];
@endphp

<header class="mx-auto mb-14 flex max-w-text flex-wrap items-baseline justify-between gap-x-6 gap-y-3">
    <a href="{{ route('home') }}" class="font-serif text-xl font-semibold text-fg no-underline">
        Arkesh Jayakumar
    </a>

    <nav aria-label="Main">
        <ul class="flex flex-wrap items-center gap-x-5 gap-y-1 text-sm">
            @foreach ($links as $link)
                <li>
                    <a
                        href="{{ $link['href'] }}"
                        @if ($link['active']) aria-current="page" @endif
                        @class([
                            'underline-offset-[6px] decoration-accent decoration-2 hover:text-accent',
                            'text-fg underline' => $link['active'],
                            'text-muted no-underline' => ! $link['active'],
                        ])
                    >{{ $link['label'] }}</a>
                </li>
            @endforeach
        </ul>
    </nav>
</header>
