<?php

namespace Tests\Unit\Services\MasterData;

use App\Modules\Operational\Master\Entities\ItemConversionEntity;
use App\Modules\Operational\Master\Entities\ItemUomEntity;
use App\Modules\Operational\Master\Models\ItemConversion;
use App\Modules\Operational\Master\Models\ItemUom;
use App\Modules\Operational\Master\Repositories\ItemConversionRepositoryInterface;
use App\Modules\Operational\Master\Services\ItemConversionService;
use Mockery;
use Tests\TestCase;

class ItemConversionServiceTest extends TestCase
{
    private $repositoryMock;
    private ItemConversionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(ItemConversionRepositoryInterface::class);
        $this->service = new ItemConversionService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_save_validates_uuids()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->save(new ItemConversionEntity(
            branch_uuid: '',
            company_uuid: 'company-uuid',
            from_unit_uuid: '',
            to_unit_uuid: 'unit-b',
            conv_ratio: 1.0,
        ));
    }

    public function test_save_validates_same_units()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->save(new ItemConversionEntity(
            branch_uuid: 'branch-uuid',
            company_uuid: 'company-uuid',
            from_unit_uuid: 'unit-a',
            to_unit_uuid: 'unit-a',
            conv_ratio: 1.0,
        ));
    }

    public function test_save_calls_repository_on_success()
    {
        $input = new ItemConversionEntity(
            branch_uuid: 'branch-uuid',
            company_uuid: 'company-uuid',
            from_unit_uuid: 'unit-a',
            to_unit_uuid: 'unit-b',
            conv_ratio: 10.0,
        );

        $this->repositoryMock->shouldReceive('save')->once()->with($input)->andReturn($input);

        $result = $this->service->save($input);
        $this->assertNotNull($result);
    }

    public function test_save_uom_validates_input()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->saveUom(new ItemConversionEntity(
            branch_uuid: 'branch-uuid',
            company_uuid: 'company-uuid',
            item_uuid: '',
            uom_code: null,
        ));
    }

    public function test_save_uom_calls_repository()
    {
        $input = new ItemConversionEntity(
            branch_uuid: 'branch-uuid',
            company_uuid: 'company-uuid',
            item_uuid: 'item-123',
            uom_code: 'BOX',
        );

        $this->repositoryMock->shouldReceive('saveUom')->once()->with($input->toArray())->andReturn(new ItemUomEntity(uuid: 'uom-1'));

        $result = $this->service->saveUom($input);
        $this->assertNotNull($result);
    }
}
