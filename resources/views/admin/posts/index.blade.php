<x-admin.layout title="Posts">
    <p class="text-sm text-muted">
        Edits here change the database only. The files in <code class="font-mono">content/posts</code> are
        the source of truth, and <code class="font-mono">php artisan db:seed</code> overwrites posts with
        matching slugs.
    </p>

    <p class="mt-5">
        <a href="{{ route('admin.posts.create') }}" class="inline-block rounded-md bg-accent px-4 py-2 text-sm font-medium text-bg no-underline hover:opacity-90">New post</a>
    </p>

    @if ($posts->isEmpty())
        <p class="mt-8 text-muted">No posts yet.</p>
    @else
        <ul class="mt-8 divide-y divide-border">
            @foreach ($posts as $post)
                <li class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-2 py-4">
                    <div>
                        <a href="{{ route('admin.posts.edit', $post) }}" class="font-serif text-lg font-semibold text-fg no-underline hover:text-accent">{{ $post->title }}</a>
                        <p class="font-mono text-[0.76rem] text-muted">
                            @if ($post->isPublished())
                                Published {{ $post->published_at->format('j M Y') }}
                            @elseif ($post->published_at)
                                Scheduled {{ $post->published_at->format('j M Y, H:i') }}
                            @else
                                Draft
                            @endif
                            @if ($post->project)
                                · {{ $post->project->name }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-4 text-sm">
                        @if ($post->isPublished())
                            <a href="{{ route('posts.show', $post) }}">View</a>
                        @endif
                        <a href="{{ route('admin.posts.edit', $post) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="cursor-pointer text-danger hover:underline">Delete</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-admin.layout>
