<?php

namespace Tests\Feature\Controllers\Operational;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Supplier;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Operational\Procurement\Models\Purchase;
use Tests\TestCase;

class PurchaseControllerTest extends TestCase
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

    public function test_can_list_purchases_for_current_company()
    {
        Purchase::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'invoice_no' => 'INV-SBX',
            'supplier_uuid' => Supplier::factory()->create([
                'branch_uuid' => $this->branch->uuid,
                'company_uuid' => $this->company->uuid,
            ])->uuid,
            'total' => 10000,
            'paid' => 10000,
            'status' => 'paid',
            'purchase_date' => time(),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/purchases');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.invoice_no', 'INV-SBX');
    }

    public function test_index_only_returns_purchases_from_authenticated_users_company()
    {
        Purchase::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'invoice_no' => 'INV-SBX',
            'supplier_uuid' => Supplier::factory()->create([
                'branch_uuid' => $this->branch->uuid,
                'company_uuid' => $this->company->uuid,
            ])->uuid,
            'total' => 10000,
            'paid' => 10000,
            'status' => 'paid',
            'purchase_date' => time(),
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        Purchase::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
            'invoice_no' => 'INV-OTH',
            'supplier_uuid' => Supplier::factory()->create([
                'branch_uuid' => $otherBranch->uuid,
                'company_uuid' => $otherCompany->uuid,
            ])->uuid,
            'total' => 10000,
            'paid' => 10000,
            'status' => 'paid',
            'purchase_date' => time(),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/purchases');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.invoice_no', 'INV-SBX');
    }

    public function test_can_create_purchase()
    {
        $supplier = Supplier::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/purchases', [
            'invoice_no' => 'INV-001',
            'supplier_uuid' => $supplier->uuid,
            'paid' => 50000,
            'items' => [
                [
                    'product_uuid' => $product->uuid,
                    'qty' => 2,
                    'price' => 25000,
                ],
            ],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('purchases', [
            'invoice_no' => 'INV-001',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $this->assertDatabaseHas('purchase_items', [
            'product_uuid' => $product->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
    }

    public function test_cannot_create_duplicate_purchase_invoice_in_same_company()
    {
        $supplier = Supplier::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        Purchase::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'invoice_no' => 'INV-DUP',
            'supplier_uuid' => $supplier->uuid,
            'total' => 10000,
            'paid' => 10000,
            'status' => 'paid',
            'purchase_date' => time(),
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/purchases', [
            'invoice_no' => 'INV-DUP',
            'supplier_uuid' => $supplier->uuid,
            'paid' => 10000,
            'items' => [
                [
                    'product_uuid' => $product->uuid,
                    'qty' => 1,
                    'price' => 10000,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['invoice_no']);
    }

    public function test_can_show_purchase_from_current_company()
    {
        $purchase = Purchase::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'invoice_no' => 'INV-SHOW',
            'supplier_uuid' => Supplier::factory()->create([
                'branch_uuid' => $this->branch->uuid,
                'company_uuid' => $this->company->uuid,
            ])->uuid,
            'total' => 10000,
            'paid' => 10000,
            'status' => 'paid',
            'purchase_date' => time(),
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/purchases/{$purchase->uuid}");

        $response->assertOk()
            ->assertJsonPath('data.uuid', $purchase->uuid);
    }

    public function test_cannot_show_purchase_from_another_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $purchase = Purchase::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
            'invoice_no' => 'INV-OTH',
            'supplier_uuid' => Supplier::factory()->create([
                'branch_uuid' => $otherBranch->uuid,
                'company_uuid' => $otherCompany->uuid,
            ])->uuid,
            'total' => 10000,
            'paid' => 10000,
            'status' => 'paid',
            'purchase_date' => time(),
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/purchases/{$purchase->uuid}");

        $response->assertNotFound();
    }
}
