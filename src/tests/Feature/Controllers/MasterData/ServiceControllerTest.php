<?php

namespace Tests\Feature\Controllers\MasterData;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Service;
use App\Modules\Core\Identity\Models\User;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
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

    public function test_can_list_services()
    {
        Service::factory()->count(5)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/services');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'total',
                ],
            ]);
    }

    public function test_index_only_returns_services_from_authenticated_users_company()
    {
        Service::factory()->create([
            'code' => 'SRV-SBX',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        Service::factory()->create([
            'code' => 'SRV-OTH',
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/services');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'SRV-SBX');
    }

    public function test_can_create_service()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/services', [
            'code' => 'SRV-001',
            'name' => 'Konsultasi Umum',
            'duration_minutes' => 30,
            'price' => 50000,
            'doctor_fee' => 25000,
            'is_active' => true,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('services', [
            'code' => 'SRV-001',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
    }

    public function test_cannot_create_duplicate_service_code_in_same_company()
    {
        Service::factory()->create([
            'code' => 'SRV-DUP',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/services', [
            'code' => 'SRV-DUP',
            'name' => 'Layanan Ganda',
            'duration_minutes' => 15,
            'price' => 25000,
            'doctor_fee' => 10000,
            'is_active' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_can_show_service()
    {
        $service = Service::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/services/{$service->uuid}");

        $response->assertOk()
            ->assertJsonPath('data.uuid', $service->uuid);
    }

    public function test_can_update_service()
    {
        $service = Service::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/services/{$service->uuid}", [
            'name' => 'Konsultasi Umum Updated',
        ]);

        $response->assertOk();
        $this->assertSame('Konsultasi Umum Updated', $service->fresh()->name);
    }

    public function test_can_delete_service()
    {
        $service = Service::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/services/{$service->uuid}");

        $response->assertNoContent();
        $this->assertNotEquals(0, $service->fresh()->deleted_at);
    }

    public function test_cannot_show_service_from_another_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $service = Service::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/services/{$service->uuid}");

        $response->assertNotFound();
    }

    public function test_cannot_update_service_from_another_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $service = Service::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/services/{$service->uuid}", [
            'name' => 'Tidak Boleh Update',
        ]);

        $response->assertNotFound();
    }

    public function test_cannot_delete_service_from_another_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $service = Service::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/services/{$service->uuid}");

        $response->assertNotFound();
        $this->assertSame(0, (int) $service->fresh()->getRawOriginal('deleted_at'));
    }
}
