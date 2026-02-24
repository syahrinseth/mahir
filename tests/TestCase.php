<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // In the test environment, point the tenant and landlord database
        // connections to the same SQLite in-memory database so that all
        // models (both landlord and tenant-scoped) use a single DB.
        // We share the PDO instance so all connections use the same
        // in-memory database (each :memory: SQLite gets its own DB).
        $pdo = DB::connection('sqlite')->getPdo();

        config([
            'database.connections.tenant' => config('database.connections.sqlite'),
            'database.connections.landlord' => config('database.connections.sqlite'),
        ]);

        DB::purge('tenant');
        DB::purge('landlord');

        DB::connection('tenant')->setPdo($pdo);
        DB::connection('landlord')->setPdo($pdo);
    }
}
