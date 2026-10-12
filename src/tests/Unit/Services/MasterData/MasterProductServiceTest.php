<?php

namespace Tests\Unit\Services\MasterData;

use App\Modules\Operational\Master\Entities\ProductEntity;
use App\Modules\Operational\Master\Repositories\ProductRepositoryInterface;
use App\Modules\Operational\Master\Services\MasterProductService;
use Mockery;
use Tests\TestCase;

class MasterProductServiceTest extends TestCase
{
    private $repositoryMock;
    private MasterProductService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(ProductRepositoryInterface::class);
        $this->service = new MasterProductService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_calls_repository_with_normalized_filter()
    {
        $filter = new ProductEntity(search: 'aspirin', search_column: 'name');
        $this->repositoryMock->shouldReceive('getPageData')->once()->with($filter)->andReturn(['items' => []]);

        $this->service->index($filter);
        $this->assertTrue(true);
    }

    public function test_save_validates_required_fields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('kode produk wajib diisi');
        $this->service->save(new ProductEntity(code: '', name: 'Produk'));
    }

    public function test_save_validates_negative_price()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('harga beli dasar tidak boleh negatif');
        $this->service->save(new ProductEntity(code: 'BRG', name: 'Produk', price_buy: -100));
    }

    public function test_save_successfully_calls_repository()
    {
        $input = new ProductEntity(
            code: 'BRG-001',
            name: 'Paracetamol',
            price_buy: 500,
            price_sell: 1000,
        );

        $this->repositoryMock->shouldReceive('save')->once()->with($input, [])->andReturn($input);

        $this->service->save($input);
        $this->assertTrue(true);
    }

    public function test_destroy_returns_error_if_has_transactions()
    {
        $entity = new ProductEntity(uuid: 'uuid-123');
        $this->repositoryMock->shouldReceive('hasTransactions')->with($entity->uuid)->andReturn(true);

        $result = $this->service->destroy($entity);

        $this->assertEquals('error', $result['status']);
        $this->assertStringContainsString('transaksi', $result['message']);
    }

    public function test_destroy_successfully_calls_repository()
    {
        $entity = new ProductEntity(uuid: 'uuid-123');
        $this->repositoryMock->shouldReceive('hasTransactions')->with($entity->uuid)->andReturn(false);
        $this->repositoryMock->shouldReceive('delete')->once()->with($entity);

        $result = $this->service->destroy($entity);

        $this->assertEquals('success', $result['status']);
    }

    public function test_import_rows_handles_valid_data()
    {
        $rows = [
            ['Kode', 'Nama', 'Barcode', 'Min Stock', 'Buy', 'Sell', 'Active'],
            ['BRG-001', 'P-1', 'BAR-1', '10', '500', '1000', 'true'],
        ];

        $this->repositoryMock->shouldReceive('getByCode')->once()->andReturn(null);
        $this->repositoryMock->shouldReceive('save')->once()->andReturn(new ProductEntity(
            code: 'BRG-001',
            name: 'P-1',
            branch_uuid: 'b-1',
            company_uuid: 'c-1',
        ));

        $result = $this->service->importRows($rows, 'b-1', 'c-1');

        $this->assertEquals(1, $result['total']);
        $this->assertEquals(1, $result['success']);
    }
}
