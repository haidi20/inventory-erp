<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Operational\Master\Entities\PriceLevelEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\PriceLevel;
use App\Modules\Operational\Master\Repositories\PriceLevelRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PriceLevelRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PriceLevelRepository $repository;
    private Company $company;
    private Branch $branch;
    private Company $otherCompany;
    private Branch $otherBranch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PriceLevelRepository();
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->otherCompany = Company::factory()->create();
        $this->otherBranch = Branch::factory()->create(['company_uuid' => $this->otherCompany->uuid]);
    }

    private function createPriceLevel(string $companyUuid, string $branchUuid, string $name): PriceLevel
    {
        return PriceLevel::create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
            'code' => 'PL-' . Str::random(4),
            'name' => $name,
            'is_active' => true,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    public function test_get_page_data_only_returns_own_company_price_levels()
    {
        $this->createPriceLevel($this->company->uuid, $this->branch->uuid, 'Retail');
        $this->createPriceLevel($this->otherCompany->uuid, $this->otherBranch->uuid, 'Grosir');

        $entity = new PriceLevelEntity(
            company_uuid: $this->company->uuid,
            page: 1,
            per_page: 10,
        );

        $result = $this->repository->getPageData($entity);

        $this->assertEquals(1, $result['total']);
    }

    public function test_find_by_uuid_returns_null_for_different_company()
    {
        $priceLevel = $this->createPriceLevel($this->otherCompany->uuid, $this->otherBranch->uuid, 'Retail');

        // Simulate query by different company by using a parameterized entity
        $entity = new PriceLevelEntity(company_uuid: $this->company->uuid);
        $found = $this->repository->findByUuid($priceLevel->uuid, $this->company->uuid);

        $this->assertNull($found);
    }

    public function test_find_by_uuid_returns_price_level_for_same_company()
    {
        $priceLevel = $this->createPriceLevel($this->company->uuid, $this->branch->uuid, 'Retail');

        $found = $this->repository->findByUuid($priceLevel->uuid, $this->company->uuid);

        $this->assertNotNull($found);
        $this->assertEquals($priceLevel->uuid, $found->uuid);
        $this->assertEquals($this->company->uuid, $found->company_uuid);
    }

    public function test_get_active_price_levels_only_returns_own_company()
    {
        $this->createPriceLevel($this->company->uuid, $this->branch->uuid, 'Retail');
        $this->createPriceLevel($this->otherCompany->uuid, $this->otherBranch->uuid, 'Grosir');

        $result = $this->repository->getActivePriceLevels($this->company->uuid);

        $this->assertCount(1, $result);
        $this->assertEquals($this->company->uuid, $result[0]->company_uuid);
    }
}
