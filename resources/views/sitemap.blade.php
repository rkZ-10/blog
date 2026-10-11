{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ route('home') }}</loc></url>
    <url><loc>{{ route('posts.index') }}</loc></url>
    <url><loc>{{ route('projects.index') }}</loc></url>
    @foreach ($posts as $post)
        <url>
            <loc>{{ route('posts.show', $post) }}</loc>
            <lastmod>{{ $post->updated_at->toAtomString() }}</lastmod>
        </url>
    @endforeach
    @foreach ($projects as $project)
        <url>
            <loc>{{ route('projects.show', $project) }}</loc>
            <lastmod>{{ $project->updated_at->toAtomString() }}</lastmod>
        </url>
    @endforeach
    @foreach ($tags as $tag)
        <url><loc>{{ route('tags.show', $tag) }}</loc></url>
    @endforeach
</urlset>
