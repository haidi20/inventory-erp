<?php

namespace Tests\Feature\Controllers\MasterData;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Tax;
use App\Modules\Core\Identity\Models\User;
use Tests\TestCase;

class TaxControllerTest extends TestCase
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

    public function test_can_list_taxes()
    {
        Tax::factory()->count(3)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/masters/taxes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'page', 'per_page'],
            ]);
    }

    public function test_index_only_returns_taxes_from_authenticated_users_company()
    {
        Tax::factory()->create([
            'tax_code' => 'PPN_A',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        Tax::factory()->create([
            'tax_code' => 'PPN_B',
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/masters/taxes');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.tax_code', 'PPN_A');
    }

    public function test_can_create_tax()
    {
        $payload = [
            'tax_code'   => 'PPH21',
            'percentage' => 5.0,
            'is_active'  => true,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/v1/masters/taxes', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('taxes', [
            'tax_code'    => 'PPH21',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
    }

    public function test_store_requires_tax_code()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/masters/taxes', [
            'percentage' => 5.0,
        ]);

        $response->assertStatus(422);
    }

    public function test_can_show_tax_from_same_company()
    {
        $tax = Tax::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/masters/taxes/{$tax->uuid}");

        $response->assertStatus(200)
            ->assertJsonPath('data.uuid', $tax->uuid);
    }

    public function test_cannot_show_tax_from_another_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $tax = Tax::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/masters/taxes/{$tax->uuid}");

        $response->assertStatus(404);
    }

    public function test_can_update_tax_from_same_company()
    {
        $tax = Tax::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'tax_code' => 'LAMA',
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/masters/taxes/{$tax->uuid}", [
            'tax_code' => 'BARU',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('taxes', [
            'uuid' => $tax->uuid,
            'tax_code' => 'BARU',
        ]);
    }

    public function test_cannot_update_tax_from_another_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $tax = Tax::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/masters/taxes/{$tax->uuid}", [
            'tax_code' => 'HACK',
        ]);

        $response->assertStatus(404);
    }

    public function test_can_delete_tax_from_same_company()
    {
        $tax = Tax::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/masters/taxes/{$tax->uuid}");

        $response->assertStatus(204);
        $this->assertNotEquals(0, $tax->fresh()->deleted_at);
    }
}
