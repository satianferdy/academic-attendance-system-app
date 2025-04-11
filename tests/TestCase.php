<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        // Double check we're in testing environment before parent setup
        if (env('APP_ENV') !== 'testing') {
            fwrite(STDERR, "\n⚠️ CRITICAL WARNING: Tests not running in testing environment! APP_ENV=" . env('APP_ENV') . "\n");
            fwrite(STDERR, "Tests aborted to protect your data.\n\n");
            exit(1);
        }

        parent::setUp();

        // Now check the database after connection has been established
        try {
            $testDb = 'academic_attendance_system_app_test';
            $actualDb = DB::connection()->getDatabaseName();

            if ($actualDb !== $testDb) {
                fwrite(STDERR, "\n⚠️ CRITICAL WARNING: Tests attempting to run against database '$actualDb' instead of test database '$testDb'!\n");
                fwrite(STDERR, "Tests aborted to protect your data.\n\n");
                exit(1);
            }
        } catch (\Exception $e) {
            fwrite(STDERR, "\n⚠️ ERROR: Could not check database name: " . $e->getMessage() . "\n");
            exit(1);
        }
    }
}
