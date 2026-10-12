<?php

namespace Tests\Feature\Infrastructure;

use Tests\TestCase;

class ReverbConfigTest extends TestCase
{
    public function test_broadcast_connection_uses_reverb(): void
    {
        $envExample = file_get_contents(dirname(__DIR__, 3).'/.env.example');

        $this->assertStringContainsString('BROADCAST_CONNECTION=reverb', $envExample);
    }

    public function test_reverb_credentials_exist_in_env_example(): void
    {
        $envExample = file_get_contents(dirname(__DIR__, 3).'/.env.example');

        $this->assertStringContainsString('REVERB_APP_ID=', $envExample);
        $this->assertStringContainsString('REVERB_APP_KEY=', $envExample);
        $this->assertStringContainsString('REVERB_APP_SECRET=', $envExample);
    }

    public function test_reverb_vite_variables_are_forwarded(): void
    {
        $envExample = file_get_contents(dirname(__DIR__, 3).'/.env.example');

        $this->assertStringContainsString('VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"', $envExample);
        $this->assertStringContainsString('VITE_REVERB_HOST="${REVERB_HOST}"', $envExample);
        $this->assertStringContainsString('VITE_REVERB_PORT="${REVERB_PORT}"', $envExample);
    }

    public function test_broadcasting_config_supports_reverb_driver(): void
    {
        $config = config('broadcasting.default');

        $this->assertSame('reverb', $config);
    }

    public function test_echo_ts_file_exists_and_uses_reverb_broadcaster(): void
    {
        $echoFile = file_get_contents(dirname(__DIR__, 3).'/resources/js/echo.ts');

        $this->assertStringContainsString('broadcaster: "reverb"', $echoFile);
        $this->assertStringContainsString('laravel-echo', $echoFile);
        $this->assertStringContainsString('pusher-js', $echoFile);
    }

    public function test_app_ts_imports_echo(): void
    {
        $appFile = file_get_contents(dirname(__DIR__, 3).'/resources/js/app.ts');

        $this->assertStringContainsString("import './echo'", $appFile);
    }
}