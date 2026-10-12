<?php

namespace Tests\Feature\Repositories\Operational;

use App\Modules\Operational\Inventory\Entities\StockTransferFilterEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Inventory\Repositories\StockTransferRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class StockTransferRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private StockTransferRepository $repository;
    private Company $company;
    private Branch $branch;
    private Company $otherCompany;
    private Branch $otherBranch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new StockTransferRepository();
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->otherCompany = Company::factory()->create();
        $this->otherBranch = Branch::factory()->create(['company_uuid' => $this->otherCompany->uuid]);
    }

    private function insertTransfer(string $companyUuid, string $branchUuid): string
    {
        $uuid = (string) Str::uuid();
        DB::table('stock_transfers')->insert([
            'uuid'                       => $uuid,
            'branch_uuid'                => $branchUuid,
            'company_uuid'               => $companyUuid,
            'source_location_uuid'      => (string) Str::uuid(),
            'destination_location_uuid' => (string) Str::uuid(),
            'no_pag'                     => 'ST-' . Str::random(6),
            'date'                       => time(),
            'status'                     => 'draft',
            'deleted_at'                 => 0,
            'created_at'                 => time(),
            'updated_at'                 => time(),
        ]);
        return $uuid;
    }

    public function test_get_page_data_only_returns_own_company_transfers()
    {
        $this->insertTransfer($this->company->uuid, $this->branch->uuid);
        $this->insertTransfer($this->otherCompany->uuid, $this->otherBranch->uuid);

        $filter = new StockTransferFilterEntity(
            company_uuid: $this->company->uuid,
            page: 1,
            per_page: 10,
        );

        $result = $this->repository->getPageData($filter);

        $this->assertEquals(1, $result->total);
    }

    public function test_get_by_uuid_returns_null_for_different_company()
    {
        $uuid = $this->insertTransfer($this->otherCompany->uuid, $this->otherBranch->uuid);

        $found = $this->repository->getByUuid(
            $uuid,
            $this->branch->uuid,
            $this->company->uuid
        );

        $this->assertNull($found);
    }

    public function test_get_by_uuid_returns_transfer_for_same_company()
    {
        $uuid = $this->insertTransfer($this->company->uuid, $this->branch->uuid);

        $found = $this->repository->getByUuid(
            $uuid,
            $this->branch->uuid,
            $this->company->uuid
        );

        $this->assertNotNull($found);
        $this->assertEquals($uuid, $found->uuid);
    }
}
