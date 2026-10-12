<?php

namespace Tests\Unit\Services;

use App\Modules\Core\Organization\Entities\CompanyEntity;
use App\Modules\Core\Organization\Repositories\CompanyRepositoryInterface;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Organization\Repositories\CompanyRepository;
use App\Modules\Core\Organization\Services\CompanyService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Mockery;
use Tests\TestCase;

class CompanyServiceTest extends TestCase
{
    protected $repositoryMock;
    protected CompanyService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repositoryMock = Mockery::mock(CompanyRepositoryInterface::class);
        $this->service = new CompanyService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_returns_repository_items_as_collection(): void
    {
        $filter = CompanyEntity::fromArray(['page' => 2, 'per_page' => 5, 'search' => 'clinic']);

        $this->repositoryMock->shouldReceive('getPageData')
            ->once()
            ->with(Mockery::on(function (CompanyEntity $entity) {
                return $entity->page === 2 && $entity->per_page === 5 && $entity->search === 'clinic';
            }))
            ->andReturn([
                'items' => [CompanyEntity::fromArray(['uuid' => '123', 'name' => 'Klinik A'])],
                'total' => 1,
            ]);

        $result = $this->service->index($filter);

        $this->assertCount(1, $result);
        $this->assertSame('Klinik A', $result->first()->name);
    }

    public function test_get_by_uuid_returns_entity(): void
    {
        $company = CompanyEntity::fromArray(['uuid' => 'uuid-123', 'name' => 'Klinik Test']);

        $this->repositoryMock->shouldReceive('findByUuid')
            ->once()
            ->with('uuid-123')
            ->andReturn($company);

        $result = $this->service->getByUuid(new CompanyEntity(uuid: 'uuid-123'));

        $this->assertNotNull($result);
        $this->assertSame('uuid-123', $result->uuid);
    }

    public function test_get_by_uuid_returns_null_if_empty(): void
    {
        $this->repositoryMock->shouldNotReceive('findByUuid');

        $result = $this->service->getByUuid(new CompanyEntity(uuid: '   '));

        $this->assertNull($result);
    }

    public function test_store_throws_exception_if_name_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nama perusahaan wajib diisi.');

        $this->service->store(new CompanyEntity(name: '   '));
    }

    public function test_store_creates_entity_and_saves(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn ($closure) => $closure());

        $company = CompanyEntity::fromArray(['name' => 'Klinik Sehat']);

        $this->repositoryMock->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (CompanyEntity $entity) {
                return $entity->name === 'Klinik Sehat'
                    && $entity->email === 'sehat@test.test'
                    && $entity->is_active === true;
            }))
            ->andReturn($company);

        $result = $this->service->store(CompanyEntity::fromArray([
            'name' => 'Klinik Sehat',
            'email' => 'sehat@test.test',
            'is_active' => true,
        ]));

        $this->assertSame('Klinik Sehat', $result->name);
    }

    public function test_update_throws_exception_if_uuid_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('UUID perusahaan wajib diisi.');

        $this->service->update(CompanyEntity::fromArray(['name' => 'Klinik A', 'uuid' => '']));
    }

    public function test_update_throws_exception_if_name_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nama perusahaan wajib diisi.');

        $this->service->update(CompanyEntity::fromArray(['uuid' => '123A', 'name' => '']));
    }

    public function test_update_saves_entity(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn ($closure) => $closure());

        $company = CompanyEntity::fromArray(['uuid' => '123A', 'name' => 'Update Klinik']);

        $this->repositoryMock->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (CompanyEntity $entity) {
                return $entity->uuid === '123A' && $entity->name === 'Update Klinik';
            }))
            ->andReturn($company);

        $result = $this->service->update(CompanyEntity::fromArray([
            'uuid' => '123A',
            'name' => 'Update Klinik',
        ]));

        $this->assertSame('123A', $result->uuid);
    }

    public function test_destroy_throws_exception_if_uuid_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('UUID perusahaan wajib diisi.');

        $this->service->destroy(new CompanyEntity(uuid: '  '));
    }

    public function test_destroy_calls_repository(): void
    {
        $this->repositoryMock->shouldReceive('delete')
            ->once()
            ->with('123A');

        $this->service->destroy(new CompanyEntity(uuid: '123A'));

        $this->assertTrue(true);
    }

    public function test_export_returns_headers_and_items(): void
    {
        $this->repositoryMock->shouldReceive('getPageData')
            ->once()
            ->with(Mockery::on(fn (CompanyEntity $entity) => $entity->per_page === 10000))
            ->andReturn([
                'items' => [CompanyEntity::fromArray(['uuid' => '123', 'name' => 'Klinik A'])],
            ]);

        $result = $this->service->export(CompanyEntity::fromArray(['search' => 'Klinik']));

        $this->assertArrayHasKey('headers', $result);
        $this->assertCount(11, $result['headers']);
        $this->assertSame('Klinik A', $result['items'][0]->name);
        $this->assertSame('master-perusahaan.xlsx', $result['filename']);
    }

    public function test_get_import_template_format(): void
    {
        $result = $this->service->getImportTemplate();

        $this->assertArrayHasKey('sheet', $result);
        $this->assertArrayHasKey('headers', $result);
        $this->assertArrayHasKey('sample_row', $result);
        $this->assertArrayHasKey('guide', $result);
    }

    public function test_get_first_active_returns_active_company(): void
    {
        $company = Company::factory()->create(['is_active' => true]);

        $service = new CompanyService(new CompanyRepository());
        $found = $service->getFirstActive();

        $this->assertNotNull($found);
        $this->assertSame($company->uuid, $found->uuid);
    }

    public function test_import_rows_handles_empty_or_faulty_rows(): void
    {
        DB::shouldReceive('transaction')
            ->atLeast()->once()
            ->andReturnUsing(fn ($closure) => $closure());

        $rows = [
            ['Nama', 'Jenis', 'Reg No'],
            [],
            ['', 'Klinik', 'REG1'],
            ['Cabang', 'Klinik', 'REG2'],
        ];

        $this->repositoryMock->shouldReceive('getPageData')
            ->once()
            ->andReturn(['items' => []]);

        $this->repositoryMock->shouldReceive('save')
            ->once()
            ->andReturn(CompanyEntity::fromArray(['name' => 'Cabang']));

        $result = $this->service->importRows($rows, 'actor-1');

        $this->assertSame(2, $result['total']);
        $this->assertSame(1, $result['success']);
        $this->assertSame(1, $result['failed']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('Nama wajib diisi', $result['errors'][0]);
    }

    public function test_import_rows_updates_if_register_no_exists(): void
    {
        DB::shouldReceive('transaction')
            ->atLeast()->once()
            ->andReturnUsing(fn ($closure) => $closure());

        $rows = [
            ['Nama', 'Jenis', 'Reg No'],
            ['Klinik Exists', 'Klinik', 'REG2'],
        ];

        $this->repositoryMock->shouldReceive('getPageData')
            ->once()
            ->andReturn(['items' => [['uuid' => 'existing-uuid']]]);

        $this->repositoryMock->shouldReceive('save')
            ->once()
            ->andReturn(CompanyEntity::fromArray(['uuid' => 'existing-uuid', 'name' => 'Klinik Exists']));

        $result = $this->service->importRows($rows, 'actor-1');

        $this->assertSame(1, $result['total']);
        $this->assertSame(1, $result['success']);
        $this->assertSame(0, $result['failed']);
    }
}
