<?php

return [

    /*
     * Crawling is off: the crawler treats every rkz-10.github.io link (including the portfolio) as
     * internal, and it can't skip routes. AppServiceProvider passes the exact list of public pages
     * from App\Services\PublicPages instead, which never includes /admin or /up.
     */
    'crawl' => false,

    'use_streaming' => false,

    'paths' => [],

    /*
     * The public folder (built assets, robots.txt, resume.pdf) plus .nojekyll, which stops GitHub
     * Pages from running Jekyll over the output.
     */
    'include_files' => [
        'public' => '',
        'resources/export/.nojekyll' => '.nojekyll',
    ],

    /*
     * index.php, .htaccess, the Vite "hot" file and the build manifests are only used by PHP.
     */
    'exclude_file_patterns' => [
        '/\.php$/',
        '/\.htaccess$/',
        '/[\/\\\\]hot$/',
        '/manifest\.json$/',
    ],

    'clean_before_export' => true,

    /*
     * Empty means the default: a dist/ folder in the project root.
     */
    'disk' => null,

    'before' => [],

    'after' => [],

];
