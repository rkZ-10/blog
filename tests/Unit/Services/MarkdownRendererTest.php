<?php

use App\Services\MarkdownRenderer;

it('renders github-flavoured tables', function () {
    $html = (new MarkdownRenderer)->render("| Driver | Store |\n| --- | --- |\n| redis | cache |");

    expect($html)->toContain('<table>')
        ->toContain('<th>Driver</th>')
        ->toContain('<td>redis</td>');
});

it('renders fenced code blocks with a language class', function () {
    $html = (new MarkdownRenderer)->render("```php\necho 'hi';\n```");

    expect($html)->toContain('<pre><code class="language-php">');
});

it('adds ids and anchor links to h2 and h3 headings only', function () {
    $html = (new MarkdownRenderer)->render("# Title\n\n## Queue workers\n\n### Retry policy");

    expect($html)->toContain('<h2 id="queue-workers">')
        ->toContain('href="#queue-workers"')
        ->toContain('<h3 id="retry-policy">')
        ->not->toContain('<h1 id=');
});

it('strips javascript links', function () {
    $html = (new MarkdownRenderer)->render('[click](javascript:alert(1))');

    expect($html)->not->toContain('javascript:');
});
