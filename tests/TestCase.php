<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    // Tambahkan method setUp ini
    protected function setUp(): void
    {
        parent::setUp();

        // Matikan Foreign Key Constraints khusus untuk database SQLite
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }
    }
}
