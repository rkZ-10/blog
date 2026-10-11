<?php

it('renders the intro, featured projects and latest posts', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSeeInOrder([
            'Notes on backend systems and AI tooling',
            'Featured projects',
            'Travel Assistant',
            'Latest posts',
            'Building this site with Laravel',
        ]);
});

it('links featured projects to their repositories', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('href="https://github.com/rkZ-10/travel-assistant"', escape: false);
});
