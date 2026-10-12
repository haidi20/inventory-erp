<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        putenv('APP_ENV=testing');
        $_ENV['APP_ENV'] = 'testing';

        parent::setUp();

        config(['cache.default' => 'array']);
        config(['permission.cache.store' => 'array']);
        config(['session.driver' => 'array']);
        $this->withoutVite();
        config(['klinik.runtime_environment' => 'sandbox']);
    }

    /**
     * Override the RefreshDatabase trait's beginDatabaseTransaction so we can
     * defensively roll back leftover PDO transaction state from a prior test
     * that crashed without cleaning up. SQLite ":memory:" reuses the same PDO
     * across tests, so without this guard a single failure cascades into
     * "There is already an active transaction" on every subsequent test.
     */
    public function beginDatabaseTransaction()
    {
        $database = $this->app->make('db');

        foreach ($this->connectionsToTransact() as $name) {
            $connection = $database->connection($name);

            try {
                $pdo = $connection->getPdo();
                while ($pdo && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
            } catch (\Throwable $e) {
                // Connection not initialized yet — nothing to do.
            }

            $reflection = new \ReflectionObject($connection);
            if ($reflection->hasProperty('transactions')) {
                $prop = $reflection->getProperty('transactions');
                $prop->setAccessible(true);
                $prop->setValue($connection, 0);
            }

            $dispatcher = $connection->getEventDispatcher();
            $connection->unsetEventDispatcher();
            $connection->beginTransaction();
            $connection->setEventDispatcher($dispatcher);
        }

        $this->beforeApplicationDestroyed(function () use ($database) {
            foreach ($this->connectionsToTransact() as $name) {
                $connection = $database->connection($name);
                $dispatcher = $connection->getEventDispatcher();
                $connection->unsetEventDispatcher();
                try {
                    $connection->rollBack();
                } catch (\Throwable $e) {
                    // Already rolled back.
                }
                $connection->setEventDispatcher($dispatcher);
                $connection->disconnect();
            }
        });
    }

    /**
     * Include semua migration dari modules/ agar SQLite in-memory
     * punya semua tabel yang dibutuhkan saat testing.
     */
    protected function migrateFreshUsing()
    {
        return [
            '--path' => [
                'database/migrations',
                'database/migrations/v1',
                'database/migrations/v2',
                'modules/Analytics/Dashboard/database/migrations',
                'modules/Analytics/Reports/database/migrations',
                'modules/Clinical/Ancillary/database/migrations',
                'modules/Clinical/Classification/database/migrations',
                'modules/Clinical/Integration/database/migrations',
                'modules/Clinical/MedicalRecord/database/migrations',
                'modules/Clinical/Pharmacy/database/migrations',
                'modules/Clinical/Registration/database/migrations',
                'modules/Core/Authorization/database/migrations',
                'modules/Core/Identity/database/migrations',
                'modules/Core/Organization/database/migrations',
                'modules/Core/Platform/database/migrations',
                'modules/Finance/Journal/database/migrations',
                'modules/Finance/Ledger/database/migrations',
                'modules/Operational/Inventory/database/migrations',
                'modules/Operational/Manufacturing/database/migrations',
                'modules/Operational/Master/database/migrations',
                'modules/Operational/Procurement/database/migrations',
                'modules/Operational/Sales/database/migrations',
            ],
            '--drop-views' => true,
        ];
    }
}
