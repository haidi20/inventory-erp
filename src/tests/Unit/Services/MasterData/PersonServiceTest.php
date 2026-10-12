<?php

namespace Tests\Unit\Services\MasterData;

use App\Modules\Operational\Master\Entities\PersonEntity;
use App\Modules\Operational\Master\Models\Person;
use App\Modules\Operational\Master\Repositories\PersonRepositoryInterface;
use App\Modules\Operational\Master\Services\PersonService;
use Mockery;
use Tests\TestCase;

class PersonServiceTest extends TestCase
{
    private $repositoryMock;
    private PersonService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(PersonRepositoryInterface::class);
        $this->service = new PersonService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_calls_repository(): void
    {
        $entity = new PersonEntity(search: 'test');
        $this->repositoryMock->shouldReceive('getPageData')->once()->with($entity)->andReturn([]);

        $this->service->index($entity);
        $this->assertTrue(true);
    }

    public function test_save_validates_required_fields(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('nama person wajib diisi');
        $this->service->save(new PersonEntity(name: '', code: 'P01'));
    }

    public function test_save_validates_unique_code(): void
    {
        $existing = new Person();
        $existing->uuid = 'other-uuid';
        $existing->name = 'Existing';
        $existing->code = 'DUPE';

        $this->repositoryMock->shouldReceive('getByCode')->andReturn($existing);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('kode person sudah digunakan');

        $this->service->save(new PersonEntity(uuid: 'my-uuid', name: 'John', code: 'DUPE'));
    }

    public function test_save_calls_repository_save(): void
    {
        $entity = new PersonEntity(name: 'John', code: 'P01');
        $this->repositoryMock->shouldReceive('getByCode')->andReturn(null);
        $saved = new Person();
        $saved->uuid = 'person-1';
        $saved->name = 'John';
        $saved->code = 'P01';

        $this->repositoryMock->shouldReceive('save')->once()->with($entity)->andReturn($saved);

        $result = $this->service->save($entity);
        $this->assertSame('P01', $result->code);
    }

    public function test_destroy_calls_repository_delete(): void
    {
        $entity = new PersonEntity(uuid: 'uuid-123');
        $this->repositoryMock->shouldReceive('delete')->once()->with($entity);

        $this->service->destroy($entity);
        $this->assertTrue(true);
    }

    public function test_import_rows_handles_valid_data(): void
    {
        $rows = [
            ['Kode', 'Nama', 'Tipe', 'Alamat', 'NPWP', 'Aktif'],
            ['P001', 'John', 'customer', 'Jl. ABC', '123', 'aktif'],
        ];

        $this->repositoryMock->shouldReceive('getByCode')->andReturn(null);
        $saved = new Person();
        $saved->uuid = 'person-1';
        $saved->name = 'John';
        $saved->code = 'P001';

        $this->repositoryMock->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PersonEntity::class))
            ->andReturn($saved);

        $result = $this->service->importRows($rows, 'b-1', 'c-1');

        $this->assertSame(1, $result['total']);
        $this->assertSame(1, $result['success']);
    }
}
