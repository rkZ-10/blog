{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title>{{ config('app.name') }}</title>
        <link>{{ route('home') }}</link>
        <description>Notes on backend systems and AI tooling.</description>
        <language>{{ strtolower(str_replace('_', '-', app()->getLocale())) }}</language>
        <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml" />
        @if ($posts->isNotEmpty())
            <lastBuildDate>{{ $posts->first()->published_at->toRssString() }}</lastBuildDate>
        @endif
        @foreach ($posts as $post)
            <item>
                <title>{{ $post->title }}</title>
                <link>{{ route('posts.show', $post) }}</link>
                <guid isPermaLink="true">{{ route('posts.show', $post) }}</guid>
                <pubDate>{{ $post->published_at->toRssString() }}</pubDate>
                @if ($post->excerpt)
                    <description>{{ $post->excerpt }}</description>
                @endif
                <content:encoded>{{ $post->bodyHtml() }}</content:encoded>
            </item>
        @endforeach
    </channel>
</rss>
