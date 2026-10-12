<?php

namespace Tests\Unit\Services\Infrastructure;

use Tests\TestCase;

class ModuleBoundaryTest extends TestCase
{
    public function test_no_boundary_violations_in_strict_mode(): void
    {
        $script = base_path("scripts/check-module-boundaries.php");

        if (!file_exists($script)) {
            $this->markTestSkipped("Boundary check script not found.");
        }

        $output = [];
        $exitCode = 0;
        exec("php " . escapeshellarg($script) . " --strict 2>&1", $output, $exitCode);

        $outputStr = implode("\n", $output);

        $this->assertSame(
            0,
            $exitCode,
            "Module boundary check failed:\n" . $outputStr
        );
        $this->assertStringContainsString("OK", $outputStr);
    }

    public function test_web_auth_service_does_not_import_model(): void
    {
        $serviceFile = base_path("modules/Core/Identity/Services/WebAuthService.php");

        if (!file_exists($serviceFile)) {
            $this->markTestSkipped("WebAuthService not found.");
        }

        $content = file_get_contents($serviceFile);

        $this->assertStringNotContainsString(
            "use App\Modules\Core\Identity\Models\User;",
            $content,
            "WebAuthService must not import User Model directly. Use UserEntity instead."
        );
    }

    public function test_product_service_does_not_import_model(): void
    {
        $serviceFile = base_path("modules/Operational/Master/Services/ProductService.php");

        if (!file_exists($serviceFile)) {
            $this->markTestSkipped("ProductService not found.");
        }

        $content = file_get_contents($serviceFile);

        $this->assertStringNotContainsString(
            "use App\Modules\Operational\Master\Models\Product;",
            $content,
            "ProductService must not import Product Model directly. Use ProductEntity instead."
        );
    }

    public function test_attempt_login_return_type_is_user_entity(): void
    {
        $serviceFile = base_path("modules/Core/Identity/Services/WebAuthService.php");

        if (!file_exists($serviceFile)) {
            $this->markTestSkipped("WebAuthService not found.");
        }

        $content = file_get_contents($serviceFile);

        $this->assertMatchesRegularExpression(
            "/function\s+attemptLogin\(.*\)\s*:\s*\?UserEntity/",
            $content,
            "attemptLogin() must declare ?UserEntity as return type."
        );
    }

    public function test_login_registered_user_return_type_is_user_entity(): void
    {
        $serviceFile = base_path("modules/Core/Identity/Services/WebAuthService.php");

        if (!file_exists($serviceFile)) {
            $this->markTestSkipped("WebAuthService not found.");
        }

        $content = file_get_contents($serviceFile);

        $this->assertMatchesRegularExpression(
            "/function\s+loginRegisteredUser\(.*\)\s*:\s*\?UserEntity/",
            $content,
            "loginRegisteredUser() must declare ?UserEntity as return type."
        );
    }

    public function test_issue_api_token_parameter_type_is_user_entity(): void
    {
        $serviceFile = base_path("modules/Core/Identity/Services/WebAuthService.php");

        if (!file_exists($serviceFile)) {
            $this->markTestSkipped("WebAuthService not found.");
        }

        $content = file_get_contents($serviceFile);

        $this->assertMatchesRegularExpression(
            "/function\s+issueApiToken\(UserEntity\s+\\\$user\)/",
            $content,
            "issueApiToken() must accept UserEntity, not User Model."
        );
    }
}
