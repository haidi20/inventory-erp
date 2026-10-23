<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use PHPUnit\Framework\TestCase;

class PlaywrightCiRuntimeTest extends TestCase
{
    /**
     * Param: Tidak ada parameter.
     *
     * Proses: Memverifikasi workflow public menjalankan Playwright melalui
     * server terkelola sehingga kegagalan start terlihat di log dan webServer
     * internal tidak menutupi penyebab timeout.
     *
     * Output: Test lulus bila script server ada, workflow memakai script
     * tersebut, config menonaktifkan webServer internal, dan log E2E tersimpan.
     */
    public function test_public_playwright_ci_uses_managed_server(): void
    {
        $rootPath = dirname(__DIR__, 4);
        $scriptPath = $rootPath.'/src/scripts/ci/run-playwright-e2e.sh';
        $workflowPath = $rootPath.'/.github/workflows/ci.yml';
        $configPath = $rootPath.'/src/playwright.config.ts';

        $this->assertFileExists($scriptPath);

        $script = (string) file_get_contents($scriptPath);
        $workflow = (string) file_get_contents($workflowPath);
        $config = (string) file_get_contents($configPath);

        $this->assertStringContainsString('php artisan serve --host=127.0.0.1 --port=8000 --no-reload', $script);
        $this->assertStringContainsString('curl --silent --fail http://127.0.0.1:8000/up', $script);
        $this->assertStringContainsString('npm run e2e:report', $script);
        $this->assertStringContainsString('bash scripts/ci/run-playwright-e2e.sh | tee ../playwright-e2e.log', $workflow);
        $this->assertStringContainsString('playwright-e2e.log', $workflow);
        $this->assertStringContainsString('const manageServerInScript = process.env.E2E_MANAGE_SERVER === \'1\';', $config);
        $this->assertStringContainsString('webServer: manageServerInScript', $config);
    }
}
