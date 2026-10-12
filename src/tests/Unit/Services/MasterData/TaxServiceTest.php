<?php

namespace Tests\Unit\Services\MasterData;

use App\Modules\Operational\Master\Entities\TaxEntity;
use App\Modules\Operational\Master\Repositories\TaxRepositoryInterface;
use App\Modules\Operational\Master\Repositories\TaxRepository;
use App\Modules\Operational\Master\Services\TaxService;
use App\Modules\Operational\Master\Models\Tax;
use Mockery;
use Tests\TestCase;

class TaxServiceTest extends TestCase
{
    private $repositoryMock;
    private TaxService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(TaxRepositoryInterface::class);
        $this->service = new TaxService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_calls_repository(): void
    {
        $filter = TaxEntity::fromArray(['search' => 'ppn']);
        $this->repositoryMock->shouldReceive('getPageData')->once()->with($filter)->andReturn(['items' => []]);

        $result = $this->service->index($filter);
        $this->assertCount(0, $result);
    }

    public function test_save_validates_tax_code(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('kode pajak wajib diisi');
        $this->service->save(new TaxEntity(tax_code: ''));
    }

    public function test_save_calls_repository_save(): void
    {
        $entity = new TaxEntity(tax_code: 'PPN11', percentage: 11);
        $this->repositoryMock->shouldReceive('save')->once()->with($entity)->andReturn($entity);

        $result = $this->service->save($entity);
        $this->assertSame('PPN11', $result->tax_code);
    }

    public function test_destroy_calls_repository_delete(): void
    {
        $entity = new TaxEntity(uuid: 'uuid-123');
        $this->repositoryMock->shouldReceive('delete')->once()->with($entity);

        $result = $this->service->destroy($entity);

        $this->assertSame('Pajak berhasil dihapus', $result['message']);
    }

    public function test_export_calls_repository_list(): void
    {
        $entity = new TaxEntity(search: '');
        $this->repositoryMock->shouldReceive('listForExport')->once()->with($entity)->andReturn([]);

        $this->service->export($entity);
        $this->assertTrue(true);
    }

    public function test_import_rows_handles_valid_data(): void
    {
        $rows = [
            ['Code', 'Percent', 'Active', 'Primary', 'PPH'],
            ['PPN', '10', 'aktif', 'ya', 'tidak'],
        ];

        $this->repositoryMock->shouldReceive('save')
            ->once()
            ->with(Mockery::type(TaxEntity::class))
            ->andReturn(new TaxEntity(tax_code: 'PPN'));

        $result = $this->service->importRows($rows, 'branch-1', 'company-1');

        $this->assertSame(1, $result['total']);
        $this->assertSame(1, $result['success']);
    }

    public function test_show_returns_tax_from_same_company()
    {
        $tax = Tax::factory()->create();

        $service = new TaxService(new TaxRepository());
        $found = $service->show(new TaxEntity(
            uuid: $tax->uuid,
            company_uuid: $tax->company_uuid,
        ));

        $this->assertNotNull($found);
        $this->assertSame($tax->uuid, $found->uuid);
    }

    public function test_find_by_uuid_returns_tax_from_same_company()
    {
        $tax = Tax::factory()->create();

        $service = new TaxService(new TaxRepository());
        $found = $service->findByUuid(new TaxEntity(
            uuid: $tax->uuid,
            company_uuid: $tax->company_uuid,
        ));

        $this->assertNotNull($found);
        $this->assertSame($tax->uuid, $found->uuid);
    }

    public function test_get_import_template_returns_tax_structure()
    {
        $service = new TaxService(new TaxRepository());
        $template = $service->getImportTemplate();

        $this->assertArrayHasKey('sheet', $template);
        $this->assertArrayHasKey('headers', $template);
        $this->assertNotEmpty($template['headers']);
    }
}
