<?php

namespace Tests\Unit\Services\MasterData;

use App\Modules\Operational\Master\Entities\PaymentTermEntity;
use App\Modules\Operational\Master\Repositories\PaymentTermRepositoryInterface;
use App\Modules\Operational\Master\Repositories\PaymentTermRepository;
use App\Modules\Operational\Master\Services\PaymentTermService;
use App\Modules\Operational\Master\Models\PaymentTerm;
use Mockery;
use Tests\TestCase;

class PaymentTermServiceTest extends TestCase
{
    private $repositoryMock;
    private PaymentTermService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(PaymentTermRepositoryInterface::class);
        $this->service = new PaymentTermService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_calls_repository(): void
    {
        $entity = PaymentTermEntity::fromArray(['search' => 'test']);
        $this->repositoryMock->shouldReceive('getPageData')->once()->with($entity)->andReturn([]);

        $this->service->index($entity);
        $this->assertTrue(true);
    }

    public function test_save_validates_name(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('nama termin wajib diisi');
        $this->service->save(new PaymentTermEntity(name: ''));
    }

    public function test_save_calls_repository_save(): void
    {
        $entity = new PaymentTermEntity(name: 'Net 30', due_days: 30);
        $this->repositoryMock->shouldReceive('save')->once()->with($entity)->andReturn($entity);

        $this->service->save($entity);
        $this->assertTrue(true);
    }

    public function test_destroy_calls_repository_delete(): void
    {
        $entity = new PaymentTermEntity(uuid: 'uuid-123');
        $this->repositoryMock->shouldReceive('delete')->once()->with($entity);

        $result = $this->service->destroy($entity);

        $this->assertSame('Termin berhasil dihapus', $result['message']);
    }

    public function test_import_rows_handles_valid_data(): void
    {
        $rows = [
            ['Name', 'Desc', 'Days', 'Active', 'Primary'],
            ['T7', 'Termin 7', '7', 'aktif', 'tidak'],
        ];

        $this->repositoryMock->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PaymentTermEntity::class))
            ->andReturn(new PaymentTermEntity(name: 'T7'));

        $result = $this->service->importRows($rows, 'branch-1', 'company-1');

        $this->assertSame(1, $result['total']);
        $this->assertSame(1, $result['success']);
    }

    public function test_show_returns_term_from_same_company()
    {
        $term = PaymentTerm::factory()->create();

        $service = new PaymentTermService(new PaymentTermRepository());
        $found = $service->show(new PaymentTermEntity(
            uuid: $term->uuid,
            company_uuid: $term->company_uuid,
        ));

        $this->assertNotNull($found);
        $this->assertSame($term->uuid, $found->uuid);
    }

    public function test_export_returns_payment_term_entities()
    {
        $term = PaymentTerm::factory()->create();

        $service = new PaymentTermService(new PaymentTermRepository());
        $result = $service->export(new PaymentTermEntity(
            company_uuid: $term->company_uuid,
        ));

        $this->assertCount(1, $result);
        $this->assertSame($term->uuid, $result[0]->uuid);
    }

    public function test_find_by_uuid_returns_term_from_same_company()
    {
        $term = PaymentTerm::factory()->create();

        $service = new PaymentTermService(new PaymentTermRepository());
        $found = $service->findByUuid(new PaymentTermEntity(
            uuid: $term->uuid,
            company_uuid: $term->company_uuid,
        ));

        $this->assertNotNull($found);
        $this->assertSame($term->uuid, $found->uuid);
    }

    public function test_get_import_template_returns_payment_term_structure()
    {
        $service = new PaymentTermService(new PaymentTermRepository());
        $template = $service->getImportTemplate();

        $this->assertSame('Data Termin', $template['sheet']);
        $this->assertContains('Nama Termin', $template['headers']);
    }
}
