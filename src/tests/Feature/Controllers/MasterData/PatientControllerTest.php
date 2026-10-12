<?php

namespace Tests\Feature\Controllers\MasterData;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Core\Identity\Models\User;
use Tests\TestCase;

class PatientControllerTest extends TestCase
{
    private User $user;
    private Branch $branch;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->user = User::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $this->withoutMiddleware();
    }

    /**
     * Test Index (Pagination)
     */
    public function test_can_list_patients()
    {
        Patient::factory()->count(5)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/patients');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'total',
                    'page',
                    'per_page'
                ]
            ]);
    }

    /**
     * Test Store (Create)
     */
    public function test_can_create_patient()
    {
        $payload = [
            'medical_record_no' => 'RM-2026-001',
            'name' => 'Budi Santoso',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-05-15',
            'phone' => '08123456789',
            'address' => 'Jl. Merdeka No. 10'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/patients', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('patients', [
            'medical_record_no' => 'RM-2026-001',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
    }

    /**
     * Test Show
     */
    public function test_can_show_patient()
    {
        $patient = Patient::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/patients/{$patient->uuid}");

        $response->assertStatus(200)
            ->assertJsonPath('data.uuid', $patient->uuid);
    }

    /**
     * Test Update
     */
    public function test_can_update_patient()
    {
        $patient = Patient::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        
        $payload = [
            'name' => 'Budi Santoso Updated'
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/patients/{$patient->uuid}", $payload);

        $response->assertStatus(200);
        $this->assertEquals('Budi Santoso Updated', $patient->fresh()->name);
    }

    /**
     * Test Delete (Soft Delete)
     */
    public function test_can_delete_patient()
    {
        $patient = Patient::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/patients/{$patient->uuid}");

        $response->assertStatus(204);
        $this->assertNotEquals(0, $patient->fresh()->deleted_at);
    }

    public function test_index_only_returns_patients_from_authenticated_users_company()
    {
        Patient::factory()->create([
            'name' => 'Pasien Sandbox',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        Patient::factory()->create([
            'name' => 'Pasien Other',
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/patients');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Pasien Sandbox');
    }

    public function test_cannot_show_patient_from_another_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $patient = Patient::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/patients/{$patient->uuid}");

        $response->assertStatus(404);
    }
}
