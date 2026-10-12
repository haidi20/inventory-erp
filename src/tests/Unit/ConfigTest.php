<?php

namespace Tests\Unit;

use Tests\TestCase;

class ConfigTest extends TestCase
{
    public function test_config_values()
    {
        echo "\nDB_CONNECTION: " . config('database.default');
        echo "\nDB_DATABASE: " . config('database.connections.sqlite.database');
        echo "\nCACHE_STORE: " . config('cache.default');
        $this->assertTrue(true);
    }
}
