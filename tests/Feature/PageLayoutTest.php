<?php

it('renders the main navigation on every page', function (string $routeName) {
    $response = $this->get(route($routeName));

    $response->assertOk()
        ->assertSee('href="'.route('posts.index').'"', escape: false)
        ->assertSee('href="'.route('projects.index').'"', escape: false)
        ->assertSee('href="https://rkz-10.github.io/"', escape: false);
})->with(['home', 'posts.index', 'projects.index']);

it('links the résumé when public/resume.pdf exists', function () {
    $this->app->usePublicPath(base_path('tests/Fixtures/public-with-resume'));

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('href="'.asset('resume.pdf').'"', escape: false)
        ->assertSee('Résumé');
});

it('hides the résumé link until public/resume.pdf exists', function () {
    $this->app->usePublicPath(base_path('tests/Fixtures'));

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertDontSee('resume.pdf', escape: false)
        ->assertDontSee('Résumé');
});

it('marks the current section as active in the navigation', function (string $routeName, string $activeLabel) {
    $response = $this->get(route($routeName));

    $response->assertOk()
        ->assertSee('aria-current="page"', escape: false)
        ->assertSeeInOrder(['aria-current="page"', $activeLabel], escape: false);
})->with([
    'blog' => ['posts.index', 'Blog'],
    'projects' => ['projects.index', 'Projects'],
]);

it('does not mark any section as active on the home page', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertDontSee('aria-current="page"', escape: false);
});

it('includes the page title before the site name', function () {
    $response = $this->get(route('projects.index'));

    $response->assertOk()
        ->assertSee('<title>Projects · '.config('app.name').'</title>', escape: false);
});

it('shows the contact email but no phone number', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('mailto:arkeshjayakumar@gmail.com', escape: false)
        ->assertDontSee('+91')
        ->assertDontSee('tel:', escape: false);
});

it('allows search engines to index pages', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertDontSee('noindex', escape: false);
});

it('renders the styled 404 page for unknown urls', function () {
    $response = $this->get('/this-page-does-not-exist');

    $response->assertNotFound()
        ->assertSee("This page doesn't exist", escape: false)
        ->assertSee('href="'.route('home').'"', escape: false);
});
