<?php

namespace Tests\Feature\Infrastructure;

use Tests\TestCase;

class MonitoringConfigTest extends TestCase
{
    public function test_pulse_is_installed(): void
    {
        $this->assertTrue(class_exists(\Laravel\Pulse\PulseServiceProvider::class));
    }

    public function test_telescope_is_installed(): void
    {
        $this->assertTrue(class_exists(\Laravel\Telescope\TelescopeServiceProvider::class));
    }

    public function test_telescope_only_registered_in_local(): void
    {
        $providers = require dirname(__DIR__, 3).'/bootstrap/providers.php';
        $telescopeRegistered = in_array(App\Providers\TelescopeServiceProvider::class, $providers);

        if (config('app.env') === 'local') {
            $this->assertTrue($telescopeRegistered, 'Telescope harus terdaftar di local');
        } else {
            $this->assertFalse($telescopeRegistered, 'Telescope tidak boleh terdaftar di production');
        }
    }

    public function test_pulse_config_exists(): void
    {
        $this->assertFileExists(dirname(__DIR__, 3).'/config/pulse.php');
    }

    public function test_pulse_migration_exists(): void
    {
        $migrations = glob(dirname(__DIR__, 3).'/database/migrations/*pulse*');
        $this->assertNotEmpty($migrations, 'Pulse migration tidak ditemukan');
    }

    public function test_redis_client_uses_predis(): void
    {
        $envExample = file_get_contents(dirname(__DIR__, 3).'/.env.example');
        $this->assertStringContainsString('REDIS_CLIENT=predis', $envExample);
    }
}