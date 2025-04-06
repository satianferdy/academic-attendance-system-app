<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

abstract class FeatureTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // CSRF hanya dimatikan untuk Feature test
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }
}
