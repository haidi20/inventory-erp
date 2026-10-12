<?php

namespace Tests\Feature\Api\Integration;

use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Feature Test untuk SatusehatController.
 * Menguji integrasi API end-to-end: mulai dari request HTTP, 
 * autentikasi, pemanggilan service, hingga verifikasi data di Database.
 */
class SatusehatControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->user = User::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        
        // Mocking config
        config(['services.satusehat.base_url' => 'https://api-stg.kemkes.go.id']);
        config(['services.satusehat.auth_url' => 'https://api-stg.kemkes.go.id/auth']);
        config(['services.satusehat.client_id' => 'test-id']);
        config(['services.satusehat.client_secret' => 'test-secret']);
    }

    /** @test */
    public function it_can_check_patient_via_api()
    {
        $nik = '1234567890123456';
        
        // Create local patient to verify sync
        Patient::factory()->create([
            'nik' => $nik,
            'ihs_number' => null,
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        Http::fake([
            'api-stg.kemkes.go.id/auth' => Http::response(['access_token' => 'mock-token'], 200),
            'api-stg.kemkes.go.id/Patient*' => Http::response([
                'resourceType' => 'Bundle',
                'entry' => [['resource' => ['id' => 'IHS-PAT-001']]]
            ], 200)
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/integration/satusehat/patient/{$nik}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', 'IHS-PAT-001');

        // Check DB sync
        $this->assertDatabaseHas('patients', [
            'nik' => $nik,
            'ihs_number' => 'IHS-PAT-001'
        ]);
    }

    /** @test */
    public function it_can_post_encounter_via_api()
    {
        Http::fake([
            'api-stg.kemkes.go.id/auth' => Http::response(['access_token' => 'mock-token'], 200),
            'api-stg.kemkes.go.id/Encounter' => Http::response([
                'resourceType' => 'Encounter',
                'id' => 'ENC-111'
            ], 201)
        ]);

        $payload = [
            'status' => 'arrived',
            'class' => ['code' => 'AMB']
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/integration/satusehat/encounter", $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', 'ENC-111');
    }

    /** @test */
    public function it_returns_400_for_invalid_nik()
    {
        // Invalid NIK (not 16 digits)
        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/integration/satusehat/patient/123");

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'nik pasien wajib 16 digit');
    }
}
