<?php

namespace Tests;
use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        // Early environment check before app creation
        // if (getenv('APP_ENV') !== 'testing' && env('APP_ENV') !== 'testing') {
        //     fwrite(STDERR, "\n⚠️ CRITICAL WARNING: Tests not running in testing environment!\n");
        //     exit(1);
        // }

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
