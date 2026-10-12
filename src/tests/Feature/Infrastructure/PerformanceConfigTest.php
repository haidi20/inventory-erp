<?php

namespace Tests\Feature\Infrastructure;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PerformanceConfigTest extends TestCase
{
    public function test_env_example_uses_redis_drivers(): void
    {
        $envPath = base_path(".env.example");
        $this->assertFileExists($envPath, ".env.example must exist.");

        $env = file_get_contents($envPath);

        $this->assertStringContainsString("CACHE_STORE=redis", $env, ".env.example must use CACHE_STORE=redis.");
        $this->assertStringContainsString("SESSION_DRIVER=redis", $env, ".env.example must use SESSION_DRIVER=redis.");
        $this->assertStringContainsString("QUEUE_CONNECTION=redis", $env, ".env.example must use QUEUE_CONNECTION=redis.");
    }

    public function test_permission_cache_uses_cache_store(): void
    {
        $permissionConfig = config("permission.cache.store");
        $this->assertNotNull($permissionConfig, "Permission cache store config must exist.");
    }

    public function test_cache_lookup_service_uses_remember(): void
    {
        Cache::put("lookup_types", "test", 60);
        $this->assertTrue(Cache::has("lookup_types"), "Cache store must be functional.");
        Cache::forget("lookup_types");
        $this->assertFalse(Cache::has("lookup_types"), "Cache must be clearable.");
    }
}
