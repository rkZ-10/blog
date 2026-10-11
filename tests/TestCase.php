<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Stub out Vite (and its @fonts directive) so feature tests don't depend on a frontend build.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }
}
