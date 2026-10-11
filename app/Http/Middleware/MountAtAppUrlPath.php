<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves the app under the path in APP_URL (e.g. https://rkz-10.github.io/blog) when the request
 * didn't arrive through a web server that already strips that prefix.
 *
 * The static export builds requests in-process for URLs like /blog/posts/{slug}. Without a base path,
 * Laravel would try to match "/blog/posts/{slug}" against the "/posts/{slug}" route and 404. This
 * re-creates the request as if index.php lived in /blog (the way PHP reports a subdirectory install),
 * so routing sees "/posts/{slug}" and every generated URL keeps the /blog prefix. It does nothing
 * when APP_URL has no path, as on Herd and in tests.
 */
class MountAtAppUrlPath
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $basePath = rtrim((string) parse_url((string) config('app.url'), PHP_URL_PATH), '/');

        if ($basePath === '' || $request->getBaseUrl() !== '' || ! $this->isWithin($request->getPathInfo(), $basePath)) {
            return $next($request);
        }

        $server = [
            'SCRIPT_NAME' => $basePath.'/index.php',
            'SCRIPT_FILENAME' => public_path('index.php'),
        ];

        // Symfony only recognises the base path when followed by a slash, so "/blog" becomes "/blog/".
        if ($request->getPathInfo() === $basePath) {
            $query = $request->server->get('QUERY_STRING');
            $server['REQUEST_URI'] = $basePath.'/'.($query ? "?{$query}" : '');
        }

        $mounted = $request->duplicate(server: array_merge($request->server->all(), $server));

        // The HTTP kernel re-binds whichever request reaches the router, so the URL generator,
        // route() and asset() all pick up the /blog base from here on.
        return $next($mounted);
    }

    private function isWithin(string $path, string $basePath): bool
    {
        return $path === $basePath || str_starts_with($path, $basePath.'/');
    }
}
