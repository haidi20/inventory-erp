<?php

namespace Tests\Feature\Repositories\Operational;

use App\Modules\Operational\Inventory\Entities\StockAdjustmentFilterEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Inventory\Repositories\StockAdjustmentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class StockAdjustmentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private StockAdjustmentRepository $repository;
    private Company $company;
    private Branch $branch;
    private Company $otherCompany;
    private Branch $otherBranch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new StockAdjustmentRepository();
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->otherCompany = Company::factory()->create();
        $this->otherBranch = Branch::factory()->create(['company_uuid' => $this->otherCompany->uuid]);
    }

    private function insertAdjustment(string $companyUuid, string $branchUuid): string
    {
        $uuid = (string) Str::uuid();
        DB::table('stock_adjustments')->insert([
            'uuid'           => $uuid,
            'branch_uuid'    => $branchUuid,
            'company_uuid'   => $companyUuid,
            'location_uuid' => (string) Str::uuid(),
            'adjustment_no'  => 'ADJ-' . Str::random(6),
            'date'           => time(),
            'status'         => 'draft',
            'created_at'     => time(),
            'updated_at'     => time(),
        ]);
        return $uuid;
    }

    public function test_get_adjustments_only_returns_own_company_records()
    {
        $this->insertAdjustment($this->company->uuid, $this->branch->uuid);
        $this->insertAdjustment($this->otherCompany->uuid, $this->otherBranch->uuid);

        $filter = new StockAdjustmentFilterEntity(
            company_uuid: $this->company->uuid,
            page: 1,
            per_page: 10,
        );

        $result = $this->repository->getAdjustments($filter);

        $this->assertEquals(1, $result->total);
    }

    public function test_get_adjustment_by_uuid_returns_null_for_different_company()
    {
        $uuid = $this->insertAdjustment($this->otherCompany->uuid, $this->otherBranch->uuid);

        $found = $this->repository->getAdjustmentByUuid(
            $uuid,
            $this->branch->uuid,
            $this->company->uuid
        );

        $this->assertNull($found);
    }

    public function test_get_adjustment_by_uuid_returns_record_for_same_company()
    {
        $uuid = $this->insertAdjustment($this->company->uuid, $this->branch->uuid);

        $found = $this->repository->getAdjustmentByUuid(
            $uuid,
            $this->branch->uuid,
            $this->company->uuid
        );

        $this->assertNotNull($found);
        $this->assertEquals($uuid, $found->uuid);
    }
}
