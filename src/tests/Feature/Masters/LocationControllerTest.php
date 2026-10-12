<?php

namespace Tests\Feature\Masters;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Operational\Master\Models\Location;
use Tests\TestCase;

/**
 * Test CRUD Location (Gudang) via web routes � real data, no mock.
 * Data dibersihkan otomatis oleh RefreshDatabase (rollback per test).
 */
class LocationControllerTest extends TestCase
{
    private User $user;
    private Company $company;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create(['deleted_at' => 0]);
        $this->branch  = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->user    = User::factory()->create([
            'company_uuid' => $this->company->uuid,
            'branch_uuid'  => $this->branch->uuid,
        ]);
        $this->withoutMiddleware();
    }

    public function test_can_list_locations_via_api(): void
    {
        Location::factory()->count(3)->create([
            'branch_uuid'  => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/master/warehouses');

        $response->assertOk()->assertJsonStructure(['data']);
        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    public function test_index_only_returns_locations_from_same_branch(): void
    {
        Location::factory()->create([
            'code'         => 'GDG-SENDIRI',
            'branch_uuid'  => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $otherCompany = Company::factory()->create();
        $otherBranch  = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        Location::factory()->create([
            'code'         => 'GDG-LAIN',
            'branch_uuid'  => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/master/warehouses');
        $response->assertOk();

        $codes = collect($response->json('data'))->pluck('code')->toArray();
        $this->assertContains('GDG-SENDIRI', $codes);
        $this->assertNotContains('GDG-LAIN', $codes);
    }

    public function test_can_create_location(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/master/warehouses', [
            'code'        => 'GDG-TEST-001',
            'name'        => 'Gudang Test',
            'description' => 'Deskripsi test',
            'is_active'   => true,
            'can_sell'    => true,
            'is_primary'  => false,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('locations', [
            'code'         => 'GDG-TEST-001',
            'branch_uuid'  => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
    }

    public function test_store_requires_code_and_name(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/master/warehouses', []);
        $response->assertStatus(422);
    }

    public function test_can_show_location(): void
    {
        $location = Location::factory()->create([
            'branch_uuid'  => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/master/warehouses/{$location->uuid}");

        $response->assertOk()->assertJsonPath('data.uuid', $location->uuid);
    }

    public function test_show_returns_404_for_nonexistent_location(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/master/warehouses/00000000-0000-0000-0000-000000000000');

        $response->assertNotFound();
    }

    public function test_can_update_location(): void
    {
        $location = Location::factory()->create([
            'branch_uuid'  => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'name'         => 'Nama Lama',
        ]);

        $response = $this->actingAs($this->user)->putJson(
            "/api/v1/master/warehouses/{$location->uuid}",
            ['code' => $location->code, 'name' => 'Nama Baru']
        );

        $response->assertOk();
        $this->assertDatabaseHas('locations', ['uuid' => $location->uuid, 'name' => 'Nama Baru']);
    }

    public function test_cannot_show_location_from_another_branch(): void
    {
        $otherCompany = Company::factory()->create();
        $otherBranch  = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $other        = Location::factory()->create([
            'branch_uuid'  => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/master/warehouses/{$other->uuid}");

        $response->assertNotFound();
    }

    public function test_can_delete_location(): void
    {
        $location = Location::factory()->create([
            'branch_uuid'  => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/master/warehouses/{$location->uuid}");

        $response->assertOk();
        $deleted = Location::withoutGlobalScopes()->find($location->uuid);
        $this->assertNotEquals(0, (int) $deleted?->getRawOriginal('deleted_at'));
    }
}
