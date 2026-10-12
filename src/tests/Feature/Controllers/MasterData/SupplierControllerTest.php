<?php

namespace Tests\Feature\Controllers\MasterData;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Supplier;
use App\Modules\Core\Identity\Models\User;
use Tests\TestCase;

class SupplierControllerTest extends TestCase
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

    public function test_can_list_suppliers()
    {
        Supplier::factory()->count(5)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/suppliers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'page', 'per_page'],
            ]);
    }

    public function test_index_only_returns_suppliers_from_authenticated_users_company()
    {
        Supplier::factory()->create([
            'name' => 'Supplier Sandbox',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        Supplier::factory()->create([
            'name' => 'Supplier Other',
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/suppliers');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Supplier Sandbox');
    }

    public function test_can_create_supplier()
    {
        $payload = [
            'name' => 'Supplier Baru',
            'phone' => '08123456',
            'email' => 'baru@supplier.test',
            'address' => 'Jl. Supplier',
            'term_days' => 14,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/v1/suppliers', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('suppliers', [
            'name' => 'Supplier Baru',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
    }

    public function test_can_show_supplier_from_same_company()
    {
        $supplier = Supplier::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/suppliers/{$supplier->uuid}");

        $response->assertStatus(200)
            ->assertJsonPath('data.uuid', $supplier->uuid);
    }

    public function test_cannot_show_supplier_from_another_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $supplier = Supplier::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/suppliers/{$supplier->uuid}");

        $response->assertStatus(404);
    }

    public function test_can_update_supplier_from_same_company()
    {
        $supplier = Supplier::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'name' => 'Nama Lama',
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/suppliers/{$supplier->uuid}", [
            'name' => 'Nama Baru',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('suppliers', [
            'uuid' => $supplier->uuid,
            'name' => 'Nama Baru',
        ]);
    }

    public function test_can_delete_supplier_from_same_company()
    {
        $supplier = Supplier::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/suppliers/{$supplier->uuid}");

        $response->assertStatus(204);
        $this->assertNotEquals(0, $supplier->fresh()->deleted_at);
    }
}
