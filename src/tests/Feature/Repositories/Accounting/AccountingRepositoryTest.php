<?php

namespace Tests\Feature\Repositories\Accounting;

use App\Modules\Finance\Journal\Entities\AccountEntity;
use App\Modules\Finance\Journal\Entities\AssetEntity;
use App\Modules\Finance\Journal\Entities\CoaEntity;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Journal\Models\Asset;
use App\Modules\Finance\Journal\Models\Coa;
use App\Modules\Finance\Journal\Models\CoaSetup;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Finance\Journal\Repositories\AccountingRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountingRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AccountingRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AccountingRepository();
    }

    public function test_create_account_saves_to_database()
    {
        $entity = AccountEntity::fromArray([
            'code' => '1000',
            'name' => 'Cash',
            'type' => 'asset',
        ]);

        $account = $this->repository->createAccount($entity);

        $this->assertDatabaseHas('accounts', [
            'uuid' => $account->uuid,
            'code' => '1000',
            'name' => 'Cash'
        ]);
    }

    public function test_get_account_by_code()
    {
        Account::factory()->create(['code' => '1101', 'name' => 'Bank A']);
        
        $account = $this->repository->getAccountByCode('1101');
        
        $this->assertNotNull($account);
        $this->assertEquals('Bank A', $account->name);
    }

    public function test_create_coa_saves_to_database()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_uuid' => $company->uuid]);
        $account = Account::factory()->create([
            'branch_uuid' => $branch->uuid,
            'company_uuid' => $company->uuid,
        ]);

        $entity = new CoaEntity(
            code: 'COA-001',
            name: 'Main COA',
            type: 'standard',
            account_uuid: $account->uuid,
            branch_uuid: $branch->uuid,
            company_uuid: $company->uuid
        );

        $coa = $this->repository->createCoa($entity);

        $this->assertDatabaseHas('coas', [
            'uuid' => $coa->uuid,
            'code' => 'COA-001'
        ]);
    }

    public function test_get_all_assets_returns_paginated_and_filtered_data()
    {
        $company = Company::factory()->create();
        $branchUuid = (string) Str::uuid();
        $otherBranchUuid = (string) Str::uuid();

        Branch::factory()->create(['uuid' => $branchUuid, 'company_uuid' => $company->uuid]);
        Branch::factory()->create(['uuid' => $otherBranchUuid, 'company_uuid' => $company->uuid]);

        Asset::factory()->count(5)->create([
            'branch_uuid' => $branchUuid,
            'company_uuid' => $company->uuid,
        ]);
        Asset::factory()->count(3)->create([
            'branch_uuid' => $otherBranchUuid,
            'company_uuid' => $company->uuid,
        ]);

        $entity = new AssetEntity(
            branch_uuid: $branchUuid,
            page: 1,
            per_page: 10
        );

        $result = $this->repository->getAllAssets($entity);

        $this->assertEquals(5, $result['total']);
        $this->assertCount(5, $result['items']);
    }

    public function test_update_coa_setup_upserts_data()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_uuid' => $company->uuid]);
        $account = Account::factory()->create([
            'branch_uuid' => $branch->uuid,
            'company_uuid' => $company->uuid,
        ]);

        $data = [
            'program_account' => 'SALES_REVENUE',
            'account_uuid' => $account->uuid,
            'branch_uuid' => $branch->uuid,
            'company_uuid' => $company->uuid,
        ];

        $setup = $this->repository->updateCoaSetup($data);

        $this->assertDatabaseHas('coa_setups', [
            'program_account' => 'SALES_REVENUE',
            'account_uuid' => $data['account_uuid']
        ]);

        // Update
        $data['note'] = 'Updated note';
        $this->repository->updateCoaSetup($data);

        $this->assertDatabaseHas('coa_setups', [
            'program_account' => 'SALES_REVENUE',
            'note' => 'Updated note'
        ]);
    }
}
