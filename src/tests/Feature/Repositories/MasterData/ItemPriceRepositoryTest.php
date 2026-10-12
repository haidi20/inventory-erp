<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\ProductPrice;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Entities\ProductPriceEntity;
use App\Modules\Operational\Master\Repositories\ItemPriceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ItemPriceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ItemPriceRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ItemPriceRepository();
    }

    public function test_upsert_price_creates_record()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_uuid' => $company->uuid]);

        $data = ProductPriceEntity::fromArray([
            'branch_uuid' => $branch->uuid,
            'company_uuid' => $company->uuid,
            'item_uuid' => (string) Str::uuid(),
            'price_level_uuid' => (string) Str::uuid(),
            'price' => 25000,
            'is_active' => true,
        ]);

        $price = $this->repository->upsertPrice($data);

        $this->assertDatabaseHas('item_prices', [
            'uuid' => $price->uuid,
            'price' => 25000
        ]);
    }

    public function test_update_base_price_updates_product()
    {
        $product = Product::factory()->create(['base_price_buy' => 1000]);
        
        $this->repository->updateBasePrice($product->uuid, 5000);

        $this->assertDatabaseHas('items', [
            'uuid' => $product->uuid,
            'base_price_buy' => 5000
        ]);
    }
}
