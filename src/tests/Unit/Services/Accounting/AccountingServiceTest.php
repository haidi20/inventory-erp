<?php

namespace Tests\Unit\Services\Accounting;

use App\Modules\Finance\Journal\Entities\AccountEntity;
use App\Modules\Finance\Journal\Entities\AssetEntity;
use App\Modules\Finance\Journal\Entities\CoaSetupEntity;
use App\Modules\Finance\Journal\Repositories\AccountingRepositoryInterface;
use App\Modules\Finance\Journal\Services\AccountingService;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Mockery;
use Tests\TestCase;

class AccountingServiceTest extends TestCase
{
    private $repositoryMock;
    private AccountingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(AccountingRepositoryInterface::class);
        $this->service = new AccountingService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_calculate_depreciation_straight_line()
    {
        $asset = new AssetEntity(
            asset_value: 10000000,
            life_year: 5,
            depreciation_method_uuid: AccountingService::DEPRECIATION_STRAIGHT_LINE
        );

        $depreciation = $this->service->calculateDepreciation($asset);

        $this->assertEquals(2000000, $depreciation);
    }

    public function test_create_asset_calculates_and_saves()
    {
        $entity = new AssetEntity(
            code: 'AST-001',
            name: 'Computer',
            asset_value: 5000000,
            life_year: 4
        );

        $assetResult = new AssetEntity(
            uuid: 'uuid-123',
            code: 'AST-001',
            name: 'Computer',
            asset_value: 5000000,
            life_year: 4,
            yearly_depreciation: 1250000
        );

        $this->repositoryMock->shouldReceive('createAsset')
            ->once()
            ->withArgs(function (AssetEntity $asset) {
                return $asset->code === 'AST-001'
                    && $asset->life_year === 4
                    && $asset->asset_value === 5000000.0
                    && $asset->yearly_depreciation === 1250000.0;
            })
            ->andReturn($assetResult);

        $result = $this->service->createAsset($entity);

        $this->assertEquals(1250000, $result->yearly_depreciation);
    }

    public function test_update_coa_setup_validates_input()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->service->updateCoaSetup(new CoaSetupEntity(
            program_name: '',
            account_uuid: ''
        ));
    }

    public function test_import_assets_handles_valid_rows()
    {
        Excel::shouldReceive('toArray')
            ->once()
            ->with(null, Mockery::type('string'))
            ->andReturn([[
                ['Kode', 'Nama'],
                ['AST-001', 'Computer'],
            ]]);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn ($closure) => $closure());

        $this->repositoryMock->shouldReceive('getAssetByCode')
            ->once()
            ->with('AST-001')
            ->andReturn(null);

        $this->repositoryMock->shouldReceive('createAsset')
            ->once()
            ->withArgs(function (AssetEntity $asset) {
                return $asset->code === 'AST-001'
                    && $asset->name === 'Computer'
                    && $asset->branch_uuid === 'branch-uuid'
                    && $asset->actor_uuid === 'user-uuid';
            })
            ->andReturn(new AssetEntity(uuid: 'asset-uuid'));

        $result = $this->service->importAssets('branch-uuid', 'user-uuid', 'fake-xlsx-content');

        $this->assertSame(['total' => 1, 'success' => 1], $result);
    }

    public function test_import_assets_rejects_empty_file_content()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File import tidak boleh kosong.');

        $this->service->importAssets('branch-uuid', 'user-uuid', '');
    }
}
