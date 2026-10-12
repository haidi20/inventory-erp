<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Operational\Master\Entities\ProductPriceEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\PriceLevel;
use App\Modules\Operational\Master\Models\ProductPrice;
use App\Modules\Operational\Master\Repositories\ItemPriceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ItemPriceRepositoryUpdatedTest extends TestCase
{
    use RefreshDatabase;

    private ItemPriceRepository $repository;
    private Company $company;
    private Branch $branch;
    private Company $otherCompany;
    private Branch $otherBranch;
    private Product $product;
    private PriceLevel $priceLevel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ItemPriceRepository();

        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->product = Product::factory()->create(['company_uuid' => $this->company->uuid]);

        $this->priceLevel = PriceLevel::create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'code' => 'PL-TEST',
            'name' => 'Test Price Level',
            'is_active' => true,
        ]);

        $this->otherCompany = Company::factory()->create();
        $this->otherBranch = Branch::factory()->create(['company_uuid' => $this->otherCompany->uuid]);
    }

    public function test_find_price_returns_null_for_different_company()
    {
        $otherProduct = Product::factory()->create(['company_uuid' => $this->otherCompany->uuid]);
        $otherPriceLevel = PriceLevel::create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->otherBranch->uuid,
            'company_uuid' => $this->otherCompany->uuid,
            'code' => 'PL-OTHER',
            'name' => 'Other Price Level',
            'is_active' => true,
        ]);

        ProductPrice::create([
            'uuid' => (string) Str::uuid(),
            'item_uuid' => $otherProduct->uuid,
            'price_level_uuid' => $otherPriceLevel->uuid,
            'branch_uuid' => $this->otherBranch->uuid,
            'company_uuid' => $this->otherCompany->uuid,
            'price' => 50000,
        ]);

        // Try to find the price as if from a different company
        $found = $this->repository->findPrice(
            $otherProduct->uuid,
            (string) Str::uuid(),
            $otherPriceLevel->uuid,
            $this->branch->uuid,
            $this->company->uuid  // company filter
        );

        $this->assertNull($found);
    }

    public function test_find_price_returns_price_for_same_company()
    {
        ProductPrice::create([
            'uuid' => (string) Str::uuid(),
            'item_uuid' => $this->product->uuid,
            'price_level_uuid' => $this->priceLevel->uuid,
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'price' => 75000,
        ]);

        $found = $this->repository->findPrice(
            $this->product->uuid,
            (string) Str::uuid(),
            $this->priceLevel->uuid,
            $this->branch->uuid,
            $this->company->uuid
        );

        $this->assertNotNull($found);
        $this->assertEquals(75000, $found->price);
    }
}
