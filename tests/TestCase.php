<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase; // Add this trait to refresh the database between tests

    protected function setUp(): void
    {
        parent::setUp();

        // Check database connection for safety
        $this->validateDatabaseConnection();
    }

    protected function validateDatabaseConnection()
    {
        try {
            $connection = DB::connection()->getName();
            $actualDb = DB::connection()->getDatabaseName();

            // For SQLite in-memory, we're always safe
            if ($connection === 'sqlite' && $actualDb === ':memory:') {
                return;
            }

            // For other connections, verify it's a test database
            $expectedDbPrefix = 'test';
            if (strpos($actualDb, $expectedDbPrefix) === false && $actualDb !== 'academic_attendance_system_app_test') {
                fwrite(STDERR, "\n⚠️ CRITICAL WARNING: Tests attempting to run against non-test database '$actualDb'!\n");
                fwrite(STDERR, "Tests aborted to protect your data. Ensure your test database name contains 'test'.\n\n");
                exit(1);
            }
        } catch (\Exception $e) {
            fwrite(STDERR, "\n⚠️ ERROR: Could not check database name: " . $e->getMessage() . "\n");
            exit(1);
        }
    }

    protected function tearDown(): void
    {
        // Clean up any global state or static properties
        // For example, if you have facades or services that maintain state:
        // YourFacade::resetState();

        parent::tearDown();
    }
}
