<?php

namespace Tests\Feature\Infrastructure;

use Tests\TestCase;
use Illuminate\Support\Facades\File;

class ViteHealthTest extends TestCase
{
    /** @test */
    public function vite_manifest_file_exists()
    {
        $manifestPath = public_path('build/manifest.json');
        
        $this->assertTrue(
            File::exists($manifestPath),
            "Vite manifest NOT found at: {$manifestPath}. Please run 'npm run build'."
        );
    }

    /** @test */
    public function login_page_resolves_without_vite_exceptions()
    {
        // Target public /login to avoid 302 redirects for health check
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertDontSee('Vite manifest not found');
    }
}
