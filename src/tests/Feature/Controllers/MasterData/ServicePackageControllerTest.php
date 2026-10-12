<?php

namespace Tests\Feature\Controllers\MasterData;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Service;
use App\Modules\Operational\Master\Models\ServicePackage;
use App\Modules\Core\Identity\Models\User;
use Tests\TestCase;

class ServicePackageControllerTest extends TestCase
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

    public function test_can_list_service_packages()
    {
        ServicePackage::factory()->count(3)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/service-packages');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'total'],
            ]);
    }

    public function test_index_only_returns_packages_from_authenticated_users_company()
    {
        ServicePackage::factory()->create([
            'code' => 'PKG-SBX',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        ServicePackage::factory()->create([
            'code' => 'PKG-OTH',
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/service-packages');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'PKG-SBX');
    }

    public function test_can_create_service_package()
    {
        $service = Service::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/service-packages', [
            'code' => 'PKG-001',
            'name' => 'Paket Medical Checkup',
            'price' => 150000,
            'is_active' => true,
            'items' => [
                [
                    'item_type' => 'service',
                    'item_uuid' => $service->uuid,
                    'qty' => 1,
                ],
            ],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('service_packages', [
            'code' => 'PKG-001',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $this->assertDatabaseHas('service_package_items', [
            'item_uuid' => $service->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
    }

    public function test_can_show_service_package()
    {
        $package = ServicePackage::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/service-packages/{$package->uuid}");

        $response->assertOk()
            ->assertJsonPath('data.uuid', $package->uuid);
    }

    public function test_can_update_service_package()
    {
        $package = ServicePackage::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/service-packages/{$package->uuid}", [
            'name' => 'Paket Updated',
        ]);

        $response->assertOk();
        $this->assertSame('Paket Updated', $package->fresh()->name);
    }

    public function test_can_delete_service_package()
    {
        $package = ServicePackage::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/service-packages/{$package->uuid}");

        $response->assertNoContent();
        $this->assertNotEquals(0, $package->fresh()->deleted_at);
    }

    public function test_cannot_show_service_package_from_another_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $package = ServicePackage::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/service-packages/{$package->uuid}");

        $response->assertNotFound();
    }
}
