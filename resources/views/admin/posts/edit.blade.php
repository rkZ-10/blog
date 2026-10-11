<x-admin.layout title="Edit post">
    <p class="mb-6 font-mono text-[0.76rem] text-muted">
        @if ($post->reading_minutes)
            {{ $post->reading_minutes }} min read ·
        @endif
        last saved {{ $post->updated_at->diffForHumans() }}
        @if ($post->isPublished())
            · <a href="{{ route('posts.show', $post) }}">View post</a>
        @endif
    </p>

    <form method="POST" action="{{ route('admin.posts.update', $post) }}">
        @method('PUT')
        @include('admin.posts._form', ['submitLabel' => 'Save post'])
    </form>
</x-admin.layout>
