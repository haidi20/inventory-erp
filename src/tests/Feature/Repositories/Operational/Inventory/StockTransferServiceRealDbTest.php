<?php

namespace Tests\Feature\Repositories\Operational\Inventory;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Inventory\Entities\StockTransferEntity;
use App\Modules\Operational\Inventory\Entities\StockTransferFilterEntity;
use App\Modules\Operational\Inventory\Entities\StockTransferItemEntity;
use App\Modules\Operational\Inventory\Repositories\StockRepository;
use App\Modules\Operational\Inventory\Repositories\StockTransferRepository;
use App\Modules\Operational\Inventory\Services\StockTransferService;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Operational\Master\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockTransferServiceRealDbTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private Company $otherCompany;
    private Branch $branch;
    private Product $product;
    private Unit $unit;
    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->otherCompany = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->actor = User::factory()->create();
        $this->product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $this->unit = Unit::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
    }

    public function test_it_creates_updates_and_reads_transfer_with_entity_items(): void
    {
        $service = $this->service();
        $entity = $this->transferEntity();

        $created = $service->CreateOrUpdate($entity, $this->company->uuid);

        $this->assertNotNull($created->uuid);
        $this->assertMatchesRegularExpression('/^PAG-\d{2}-\d{2}-\d{5}$/', (string) $created->document_no);
        $this->assertDatabaseHas('stock_transfers', [
            'uuid' => $created->uuid,
            'company_uuid' => $this->company->uuid,
            'branch_uuid' => $this->branch->uuid,
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('stock_transfer_items', [
            'transfer_uuid' => $created->uuid,
            'product_uuid' => $this->product->uuid,
            'unit_uuid' => $this->unit->uuid,
            'qty' => 5,
        ]);

        $created->items = [
            new StockTransferItemEntity(
                product_uuid: $this->product->uuid,
                qty: 8,
                unit_uuid: $this->unit->uuid,
            ),
        ];
        $updated = $service->CreateOrUpdate($created, $this->company->uuid);

        $this->assertSame($created->uuid, $updated->uuid);
        $this->assertSame(8.0, (float) $updated->items[0]->qty);
        $this->assertSame(1, DB::table('stock_transfer_items')
            ->where('transfer_uuid', $created->uuid)
            ->count());
    }

    public function test_it_posts_transfer_once_with_out_and_in_movements(): void
    {
        $service = $this->service();
        $created = $service->CreateOrUpdate($this->transferEntity(), $this->company->uuid);

        $posted = $service->Post(new StockTransferEntity(
            uuid: $created->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            actor_uuid: $this->actor->uuid,
        ));

        $this->assertSame('posted', $posted->status->value);
        $this->assertSame(1, DB::table('stock_movements')->where([
            'reference_uuid' => $created->uuid,
            'type' => 'OUT',
            'product_uuid' => $this->product->uuid,
        ])->count());
        $this->assertSame(1, DB::table('stock_movements')->where([
            'reference_uuid' => $created->uuid,
            'type' => 'IN',
            'product_uuid' => $this->product->uuid,
        ])->count());

        $service->Post(new StockTransferEntity(
            uuid: $created->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            actor_uuid: $this->actor->uuid,
        ));

        $this->assertSame(2, DB::table('stock_movements')
            ->where('reference_uuid', $created->uuid)
            ->count());
    }

    public function test_it_scopes_reads_and_soft_deletes_by_company_and_branch(): void
    {
        $otherBranch = Branch::factory()->create(['company_uuid' => $this->otherCompany->uuid]);
        $repository = new StockTransferRepository();
        $created = $this->service()->CreateOrUpdate($this->transferEntity(), $this->company->uuid);

        $this->assertNull($repository->getByUuid(
            $created->uuid,
            $otherBranch->uuid,
            $this->otherCompany->uuid
        ));
        $this->assertSame(1, $repository->getPageData(new StockTransferFilterEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
        ))->total);

        $this->assertTrue($repository->delete(
            $created->uuid,
            $this->branch->uuid,
            $this->company->uuid,
            $this->actor->uuid,
        ));

        $this->assertNull($repository->getByUuid(
            $created->uuid,
            $this->branch->uuid,
            $this->company->uuid
        ));
        $this->assertDatabaseHas('stock_transfers', [
            'uuid' => $created->uuid,
            'deleted_at' => time(),
        ]);
    }

    private function service(): StockTransferService
    {
        return new StockTransferService(
            new StockTransferRepository(),
            new StockRepository(),
        );
    }

    private function transferEntity(): StockTransferEntity
    {
        return new StockTransferEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            source_location_uuid: $this->location('SRC')->uuid,
            destination_location_uuid: $this->location('DST')->uuid,
            date: time(),
            items: [
                new StockTransferItemEntity(
                    product_uuid: $this->product->uuid,
                    qty: 5,
                    unit_uuid: $this->unit->uuid,
                ),
            ],
            actor_uuid: $this->actor->uuid,
        );
    }

    private function location(string $prefix): Location
    {
        static $locations = [];

        return $locations[$prefix] ??= Location::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'code' => $prefix . '-' . $this->branch->uuid,
        ]);
    }
}
