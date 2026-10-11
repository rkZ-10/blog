@props(['posts', 'empty' => 'No posts yet. The first ones are on their way.'])

@if ($posts->isEmpty())
    <p class="text-muted">{{ $empty }}</p>
@else
    <ul {{ $attributes->class('divide-y divide-border') }}>
        @foreach ($posts as $post)
            <x-post-list-item :post="$post" />
        @endforeach
    </ul>
@endif
