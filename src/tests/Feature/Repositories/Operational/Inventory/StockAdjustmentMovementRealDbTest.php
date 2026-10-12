<?php

namespace Tests\Feature\Repositories\Operational\Inventory;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Inventory\Entities\StockAdjustmentEntity;
use App\Modules\Operational\Inventory\Entities\StockAdjustmentItemEntity;
use App\Modules\Operational\Inventory\Entities\StockMovementEntity;
use App\Modules\Operational\Inventory\Enums\StockMovementReferenceType;
use App\Modules\Operational\Inventory\Models\ItemOpeningBalance;
use App\Modules\Operational\Inventory\Repositories\InventoryActionRepository;
use App\Modules\Operational\Inventory\Repositories\StockAdjustmentRepository;
use App\Modules\Operational\Inventory\Repositories\StockBalanceRepository;
use App\Modules\Operational\Inventory\Repositories\StockRepository;
use App\Modules\Operational\Inventory\Services\InventoryPublicService;
use App\Modules\Operational\Inventory\Services\StockBalanceService;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Operational\Master\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class StockAdjustmentMovementRealDbTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private Branch $branch;
    private Product $product;
    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $this->actor = User::factory()->create();
    }

    public function test_inventory_action_repository_creates_and_deletes_real_movement(): void
    {
        $entity = $this->movementEntity('adjust', (string) Str::uuid());
        $movement = (new InventoryActionRepository())->createMovement($entity);

        $this->assertDatabaseHas('stock_movements', [
            'uuid' => $entity->uuid,
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'product_uuid' => $this->product->uuid,
            'type' => 'adjust',
            'reference_uuid' => $entity->ref_uuid,
            'created_by' => $this->actor->uuid,
        ]);
        $this->assertSame($entity->uuid, $movement->uuid);
        $this->assertSame($this->actor->uuid, $movement->created_by);

        (new InventoryActionRepository())->deleteByReference(
            StockMovementReferenceType::ADJUSTMENT->value,
            $entity->ref_uuid
        );
        $this->assertDatabaseMissing('stock_movements', ['uuid' => $entity->uuid]);
    }

    public function test_stock_repository_saves_movement_using_actual_schema(): void
    {
        $entity = $this->movementEntity('IN', (string) Str::uuid());
        $saved = (new StockRepository())->saveMovement($entity);

        $this->assertDatabaseHas('stock_movements', [
            'uuid' => $saved->uuid,
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'product_uuid' => $this->product->uuid,
            'type' => 'IN',
            'price' => 1500.0,
            'created_by' => $this->actor->uuid,
        ]);
    }

    public function test_adjustment_is_created_posted_and_idempotent_with_real_movement(): void
    {
        $service = new StockBalanceService(
            new StockRepository(),
            new StockAdjustmentRepository()
        );
        $created = $service->CreateAdjustment($this->adjustmentEntity());

        $this->assertDatabaseHas('stock_adjustments', [
            'uuid' => $created->uuid,
            'company_uuid' => $this->company->uuid,
            'branch_uuid' => $this->branch->uuid,
        ]);
        $this->assertDatabaseHas('stock_adjustment_items', [
            'adjustment_uuid' => $created->uuid,
            'item_uuid' => $this->product->uuid,
            'adjustment_qty' => 4,
        ]);

        $posted = $service->PostAdjustment(new StockAdjustmentEntity(
            uuid: $created->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            actor_uuid: $this->actor->uuid
        ));

        $this->assertSame('posted', $posted->status->value);
        $this->assertDatabaseHas('stock_movements', [
            'product_uuid' => $this->product->uuid,
            'type' => 'IN',
            'reference_type' => StockMovementReferenceType::ADJUSTMENT->value,
            'reference_uuid' => $created->uuid,
            'qty' => 4,
        ]);

        $reposted = $service->PostAdjustment(new StockAdjustmentEntity(
            uuid: $created->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            actor_uuid: $this->actor->uuid
        ));

        $this->assertSame('posted', $reposted->status->value);
        $this->assertSame(1, DB::table('stock_movements')
            ->where('reference_uuid', $created->uuid)
            ->count());
    }

    public function test_public_service_can_delete_opening_balance(): void
    {
        $opening = ItemOpeningBalance::create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'item_uuid' => $this->product->uuid,
            'location_uuid' => $this->location()->uuid,
            'period_month' => now()->format('Y-m'),
            'opening_qty' => 3,
            'opening_unit_cost' => 1000,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        (new InventoryPublicService(new StockRepository(), new InventoryActionRepository()))
            ->deleteItemOpeningBalance($opening->uuid);

        $this->assertDatabaseMissing('item_opening_balances', ['uuid' => $opening->uuid]);
    }

    public function test_stock_balance_repository_reports_scope_and_movement_summary(): void
    {
        DB::table('stock_movements')->insert([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'product_uuid' => $this->product->uuid,
            'location_uuid' => $this->location()->uuid,
            'qty' => 7,
            'type' => 'IN',
            'created_at' => time(),
            'updated_at' => time(),
            'deleted_at' => 0,
        ]);

        $rows = (new StockBalanceRepository())->getStockBalanceReport([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'location_uuid' => null,
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame(7.0, (float) $rows[0]->current_balance);
        $this->assertSame(7.0, (float) $rows[0]->qty_in);
    }

    private function movementEntity(string $type, string $refUuid): StockMovementEntity
    {
        return new StockMovementEntity(
            uuid: (string) Str::uuid(),
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            product_uuid: $this->product->uuid,
            location_uuid: $this->location()->uuid,
            date: time(),
            type: $type,
            ref_type: StockMovementReferenceType::ADJUSTMENT,
            ref_uuid: $refUuid,
            unit_uuid: null,
            qty: 3,
            cost_price: 1500,
            created_by: $this->actor->uuid,
        );
    }

    private function adjustmentEntity(): StockAdjustmentEntity
    {
        return new StockAdjustmentEntity(
            uuid: (string) Str::uuid(),
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            location_uuid: $this->location()->uuid,
            date: time(),
            items: [
                new StockAdjustmentItemEntity(
                    product_uuid: $this->product->uuid,
                    measurement_uuid: '',
                    adjustment_qty: 4,
                ),
            ],
            actor_uuid: $this->actor->uuid,
        );
    }

    private function location()
    {
        static $location = null;

        return $location ??= \App\Modules\Operational\Master\Models\Location::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
    }
}
