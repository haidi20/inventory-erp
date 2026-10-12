<?php

namespace Tests\Feature\Accounting;

use App\Modules\Core\Identity\Models\User;
use Database\Seeders\V1\DemoSeeder;
use Database\Seeders\V1\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SatusehatIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RbacSeeder::class,
            DemoSeeder::class,
        ]);

        $this->admin = User::where('email', 'admin@klinik.local')->firstOrFail();
        $this->actingAs($this->admin);
    }

    public function test_satusehat_pages_are_accessible_for_authenticated_user(): void
    {
        foreach ([
            '/satusehat',
            '/satusehat/sync',
            '/satusehat/encounter',
            '/satusehat/fhir',
            '/satusehat/config',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_satusehat_settings_can_be_updated_from_settings_page(): void
    {
        $response = $this->from(route('satusehat.settings'))->post('/satusehat/settings', [
            'org_uuid' => 'ORG-TEST-001',
            'client_uuid' => 'CLIENT-TEST-001',
            'client_secret' => 'SECRET-TEST-001',
            'base_url' => 'https://example.test/fhir',
            'auth_url' => 'https://example.test/oauth',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('satusehat.settings'));

        $this->assertDatabaseHas('satusehat_settings', [
            'org_uuid' => 'ORG-TEST-001',
            'client_uuid' => 'CLIENT-TEST-001',
            'base_url' => 'https://example.test/fhir',
            'auth_url' => 'https://example.test/oauth',
            'is_active' => 1,
        ]);

        $this->assertEquals('SECRET-TEST-001', DB::table('satusehat_settings')->where('org_uuid', 'ORG-TEST-001')->value('client_secret'));
    }
}
