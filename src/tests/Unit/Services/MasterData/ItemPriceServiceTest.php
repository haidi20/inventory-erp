<?php

namespace Tests\Unit\Services\MasterData;

use App\Modules\Operational\Master\Repositories\ItemPriceRepositoryInterface;
use App\Modules\Operational\Master\Services\ItemPriceService;
use Mockery;
use Tests\TestCase;

class ItemPriceServiceTest extends TestCase
{
    private $repositoryMock;
    private ItemPriceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(ItemPriceRepositoryInterface::class);
        $this->service = new ItemPriceService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_export_prices_returns_headers_and_rows()
    {
        $this->repositoryMock->shouldReceive('listForExport')->andReturn([]);
        $this->repositoryMock->shouldReceive('getActivePriceLevels')->andReturn([]);

        $result = $this->service->exportPrices([]);

        $this->assertArrayHasKey('headers', $result);
        $this->assertArrayHasKey('rows', $result);
    }

    public function test_import_rows_validates_input()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->importExcel([], 'actor-uuid');
    }

    public function test_import_rows_processes_data()
    {
        $rows = [
            ['KODE ITEM', 'NAMA ITEM', 'HARGA BELI (BASE)', 'HARGA JUAL (BASE)'],
            ['ICO-001', 'Item 1', 1000, 1500]
        ];

        $this->repositoryMock->shouldReceive('getActivePriceLevels')->andReturn([]);
        $this->repositoryMock->shouldReceive('getByItemCode')->andReturn(['uuid' => 'item-uuid']);
        $this->repositoryMock->shouldReceive('updateBasePrices')->once();

        $result = $this->service->importExcel($rows, 'actor-uuid');

        $this->assertEquals(1, $result['success_count']);
    }
}
