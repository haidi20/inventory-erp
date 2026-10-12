<?php

namespace Tests\Feature\Controllers\MasterData;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Operational\Master\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Branch $branch;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create();
        $this->branch  = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->user    = User::factory()->create([
            'branch_uuid'  => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $this->withoutMiddleware();
    }

    public function test_can_list_warehouses(): void
    {
        Location::factory()->count(3)->create([
            'branch_uuid'  => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/master/warehouses');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_index_only_returns_warehouses_from_same_branch(): void
    {
        Location::factory()->create([
            'code'         => 'UTAMA',
            'branch_uuid'  => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch  = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        Location::factory()->create([
            'code'         => 'OTHER',
            'branch_uuid'  => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/master/warehouses');
        $response->assertStatus(200);

        $codes = collect($response->json('data'))->pluck('code')->toArray();
        $this->assertContains('UTAMA', $codes);
        $this->assertNotContains('OTHER', $codes);
    }

    public function test_can_create_warehouse(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/master/warehouses', [
            'code'        => 'WH001',
            'name'        => 'Gudang Test',
            'description' => 'Keterangan',
            'is_active'   => true,
            'can_sell'    => true,
            'is_primary'  => false,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('locations', [
            'code'         => 'WH001',
            'branch_uuid'  => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
    }

    public function test_store_requires_code_and_name(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/master/warehouses', []);
        $response->assertStatus(422);
    }

    public function test_can_show_warehouse(): void
    {
        $wh   = Location::factory()->create([
            'branch_uuid'  => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $uuid = $wh->uuid;

        $response = $this->actingAs($this->user)->getJson("/api/v1/master/warehouses/{$uuid}");
        $response->assertStatus(200)
            ->assertJsonPath('data.uuid', $wh->uuid);
    }

    public function test_can_update_warehouse(): void
    {
        $wh   = Location::factory()->create([
            'branch_uuid'  => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'name'         => 'Lama',
        ]);
        $uuid = $wh->uuid;
        $code = $wh->code;

        $response = $this->actingAs($this->user)->putJson("/api/v1/master/warehouses/{$uuid}", [
            'code' => $code,
            'name' => 'Baru',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('locations', ['uuid' => $wh->uuid, 'name' => 'Baru']);
    }

    public function test_can_delete_warehouse(): void
    {
        $wh   = Location::factory()->create([
            'branch_uuid'  => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $uuid = $wh->uuid;

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/master/warehouses/{$uuid}");
        $response->assertStatus(200);
        $this->assertNotEquals(0, Location::withoutGlobalScopes()->find($wh->uuid)?->deleted_at);
    }
}