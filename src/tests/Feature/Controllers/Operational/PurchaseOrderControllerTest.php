<?php

namespace Tests\Feature\Controllers\Operational;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Supplier;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Operational\Procurement\Models\PurchaseOrder;
use Illuminate\Support\Str;
use Tests\TestCase;

class PurchaseOrderControllerTest extends TestCase
{
    private User $user;
    private Branch $branch;
    private Company $company;
    private Supplier $supplier;
    private Location $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->user = User::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $this->supplier = Supplier::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $this->warehouse = Location::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $this->withoutMiddleware();
    }

    private function makePO(array $override = []): PurchaseOrder
    {
        return PurchaseOrder::create(array_merge([
            'uuid'           => (string) Str::uuid(),
            'branch_uuid'    => $this->branch->uuid,
            'company_uuid'   => $this->company->uuid,
            'supplier_uuid'  => $this->supplier->uuid,
            'location_uuid' => $this->warehouse->uuid,
            'document_no'    => 'PO-' . Str::random(6),
            'date'           => time(),
            'status'         => 'draft',
            'created_at'     => time(),
            'updated_at'     => time(),
        ], $override));
    }

    public function test_can_list_purchase_orders()
    {
        $this->makePO();
        $this->makePO();

        $response = $this->actingAs($this->user)->getJson('/api/v1/operational/purchase-orders');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta' => ['total', 'page', 'per_page']]);
    }

    public function test_index_only_returns_purchase_orders_from_authenticated_users_company()
    {
        $this->makePO(['document_no' => 'PO-MINE']);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $otherSupplier = Supplier::factory()->create(['company_uuid' => $otherCompany->uuid, 'branch_uuid' => $otherBranch->uuid]);
        $otherWarehouse = Location::factory()->create(['company_uuid' => $otherCompany->uuid, 'branch_uuid' => $otherBranch->uuid]);
        PurchaseOrder::create([
            'uuid'           => (string) Str::uuid(),
            'branch_uuid'    => $otherBranch->uuid,
            'company_uuid'   => $otherCompany->uuid,
            'supplier_uuid'  => $otherSupplier->uuid,
            'location_uuid' => $otherWarehouse->uuid,
            'document_no'    => 'PO-OTHER',
            'date'           => time(),
            'status'         => 'draft',
            'created_at'     => time(),
            'updated_at'     => time(),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/operational/purchase-orders');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.document_no', 'PO-MINE');
    }

    public function test_can_show_purchase_order_from_same_company()
    {
        $po = $this->makePO();

        $response = $this->actingAs($this->user)->getJson("/api/v1/operational/purchase-orders/{$po->uuid}");

        $response->assertStatus(200)
            ->assertJsonPath('data.uuid', $po->uuid);
    }

    public function test_cannot_show_purchase_order_from_another_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $otherSupplier = Supplier::factory()->create(['company_uuid' => $otherCompany->uuid, 'branch_uuid' => $otherBranch->uuid]);
        $otherWarehouse = Location::factory()->create(['company_uuid' => $otherCompany->uuid, 'branch_uuid' => $otherBranch->uuid]);
        $po = PurchaseOrder::create([
            'uuid'           => (string) Str::uuid(),
            'branch_uuid'    => $otherBranch->uuid,
            'company_uuid'   => $otherCompany->uuid,
            'supplier_uuid'  => $otherSupplier->uuid,
            'location_uuid' => $otherWarehouse->uuid,
            'document_no'    => 'PO-NOTMINE',
            'date'           => time(),
            'status'         => 'draft',
            'created_at'     => time(),
            'updated_at'     => time(),
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/operational/purchase-orders/{$po->uuid}");

        $response->assertStatus(404);
    }
}
