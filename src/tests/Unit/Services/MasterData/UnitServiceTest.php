<?php

namespace Tests\Unit\Services\MasterData;

use App\Modules\Operational\Master\Entities\UnitEntity;
use App\Modules\Operational\Master\Repositories\UnitRepositoryInterface;
use App\Modules\Operational\Master\Repositories\UnitRepository;
use App\Modules\Operational\Master\Services\UnitService;
use App\Modules\Operational\Master\Models\Unit;
use Mockery;
use Tests\TestCase;

class UnitServiceTest extends TestCase
{
    private $repositoryMock;
    private UnitService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(UnitRepositoryInterface::class);
        $this->service = new UnitService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_calls_repository_with_entity(): void
    {
        $filter = UnitEntity::fromArray(['search' => 'kg', 'page' => 1]);
        $this->repositoryMock->shouldReceive('getPageData')->once()
            ->with(Mockery::on(fn (UnitEntity $entity) => $entity->search === 'kg'))
            ->andReturn([]);

        $this->service->index($filter);
        $this->assertTrue(true);
    }

    public function test_save_validates_required_fields(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('kode satuan wajib diisi');
        $this->service->save(new UnitEntity(code: ''));
    }

    public function test_save_validates_name_field(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('nama satuan wajib diisi');
        $this->service->save(new UnitEntity(code: 'PCS', name: ''));
    }

    public function test_save_checks_duplicate_code(): void
    {
        $existing = new UnitEntity(uuid: 'uuid-old', code: 'PCS', name: 'Pieces');

        $this->repositoryMock->shouldReceive('getByCode')->andReturn($existing);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('sudah digunakan');

        $this->service->save(new UnitEntity(code: 'PCS', name: 'Pieces', uuid: 'uuid-new'));
    }

    public function test_save_successfully_calls_repository(): void
    {
        $entity = new UnitEntity(code: 'BOX', name: 'Box', branch_uuid: 'b-1', company_uuid: 'c-1');

        $this->repositoryMock->shouldReceive('getByCode')->andReturn(null);
        $this->repositoryMock->shouldReceive('save')->once()->with($entity)->andReturn($entity);

        $result = $this->service->save($entity);
        $this->assertSame('BOX', $result->code);
    }

    public function test_destroy_calls_repository_delete(): void
    {
        $entity = new UnitEntity(uuid: 'u-1', branch_uuid: 'b-1', company_uuid: 'c-1');
        $this->repositoryMock->shouldReceive('delete')->once()->with($entity);

        $this->service->destroy($entity);
        $this->assertTrue(true);
    }

    public function test_import_rows_handles_valid_data(): void
    {
        $rows = [
            ['Kode', 'Nama', 'Simbol', 'Desc', 'Aktif'],
            ['KG', 'Kilogram', 'kg', 'Berat', 'aktif'],
            ['', 'Invalid', '', '', ''],
        ];

        $this->repositoryMock->shouldReceive('getByCode')->andReturn(null);
        $this->repositoryMock->shouldReceive('save')
            ->once()
            ->with(Mockery::type(UnitEntity::class))
            ->andReturn(new UnitEntity(code: 'KG', name: 'Kilogram'));

        $result = $this->service->importRows($rows, 'b-1', 'c-1');

        $this->assertSame(1, $result['total']);
        $this->assertSame(1, $result['success']);
    }

    public function test_get_by_uuid_returns_matching_unit()
    {
        $unit = Unit::factory()->create();

        $service = new UnitService(new UnitRepository());
        $found = $service->getByUuid(new UnitEntity(
            uuid: $unit->uuid,
            branch_uuid: $unit->branch_uuid,
            company_uuid: $unit->company_uuid,
        ));

        $this->assertNotNull($found);
        $this->assertSame($unit->uuid, $found->uuid);
    }

    public function test_get_import_template_returns_satuan_structure()
    {
        $service = new UnitService(new UnitRepository());
        $template = $service->getImportTemplate();

        $this->assertSame('Data Satuan', $template['sheet']);
        $this->assertContains('Kode Satuan', $template['headers']);
    }
}
