<?php

namespace Tests\Feature\Controllers\Operational;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Operational\Master\Models\Service;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Operational\Sales\Models\Sale;
use Tests\TestCase;

class SaleControllerTest extends TestCase
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

    public function test_can_list_sales_for_current_company()
    {
        Sale::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'sale_no' => 'SALE-SBX',
            'total_amount' => 10000,
            'paid' => 10000,
            'change' => 0,
            'payment_status' => 'paid',
            'sale_at' => time(),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/sales');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sale_no', 'SALE-SBX');
    }

    public function test_index_only_returns_sales_from_authenticated_users_company()
    {
        Sale::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'sale_no' => 'SALE-SBX',
            'total_amount' => 10000,
            'paid' => 10000,
            'change' => 0,
            'payment_status' => 'paid',
            'sale_at' => time(),
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        Sale::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
            'sale_no' => 'SALE-OTH',
            'total_amount' => 10000,
            'paid' => 10000,
            'change' => 0,
            'payment_status' => 'paid',
            'sale_at' => time(),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/sales');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sale_no', 'SALE-SBX');
    }

    public function test_can_create_sale_with_service_item()
    {
        $patient = Patient::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $cashier = Employee::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $service = Service::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/sales', [
            'sale_no' => 'SALE-001',
            'patient_uuid' => $patient->uuid,
            'cashier_uuid' => $cashier->uuid,
            'paid' => 50000,
            'items' => [
                [
                    'item_type' => 'service',
                    'item_uuid' => $service->uuid,
                    'qty' => 1,
                    'price' => 50000,
                ],
            ],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('sales', [
            'sale_no' => 'SALE-001',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $this->assertDatabaseHas('sale_items', [
            'item_uuid' => $service->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
    }

    public function test_cannot_create_duplicate_sale_number_in_same_company()
    {
        Sale::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'sale_no' => 'SALE-DUP',
            'total_amount' => 10000,
            'paid' => 10000,
            'change' => 0,
            'payment_status' => 'paid',
            'sale_at' => time(),
        ]);

        $service = Service::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/sales', [
            'sale_no' => 'SALE-DUP',
            'paid' => 50000,
            'items' => [
                [
                    'item_type' => 'service',
                    'item_uuid' => $service->uuid,
                    'qty' => 1,
                    'price' => 50000,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sale_no']);
    }

    public function test_can_show_sale_from_current_company()
    {
        $sale = Sale::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'sale_no' => 'SALE-SHOW',
            'total_amount' => 10000,
            'paid' => 10000,
            'change' => 0,
            'payment_status' => 'paid',
            'sale_at' => time(),
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/sales/{$sale->uuid}");

        $response->assertOk()
            ->assertJsonPath('data.uuid', $sale->uuid);
    }

    public function test_cannot_show_sale_from_another_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $sale = Sale::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
            'sale_no' => 'SALE-OTH',
            'total_amount' => 10000,
            'paid' => 10000,
            'change' => 0,
            'payment_status' => 'paid',
            'sale_at' => time(),
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/sales/{$sale->uuid}");

        $response->assertNotFound();
    }
}
