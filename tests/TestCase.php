<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        // // Clear config cache before test runs
        // if (PHP_SAPI !== 'phpdbg') { // Skip during code coverage runs
        //     Artisan::call('config:clear');
        // }

        parent::setUp();

        // Check if using SQLite and it's in-memory
        if (config('database.default') === 'sqlite' &&
            config('database.connections.sqlite.database') === ':memory:') {
            // Run migrations for in-memory database
            Artisan::call('migrate');
        } else {
            // For non-SQLite connections, continue with your safety checks
            $this->validateDatabaseConnection();
        }
    }

    protected function validateDatabaseConnection()
    {
        try {
            $connection = DB::connection()->getName();

            // If using SQLite for testing, we're good
            if ($connection === 'sqlite') {
                return;
            }

            // For other connections, verify it's a test database
            $expectedDb = 'academic_attendance_system_app_test';
            $actualDb = DB::connection()->getDatabaseName();

            if ($actualDb !== $expectedDb) {
                fwrite(STDERR, "\n⚠️ CRITICAL WARNING: Tests attempting to run against database '$actualDb' instead of test database!\n");
                fwrite(STDERR, "Tests aborted to protect your data.\n\n");
                exit(1);
            }
        } catch (\Exception $e) {
            fwrite(STDERR, "\n⚠️ ERROR: Could not check database name: " . $e->getMessage() . "\n");
            exit(1);
        }
    }
}
