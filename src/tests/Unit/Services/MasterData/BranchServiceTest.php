<?php

namespace Tests\Unit\Services\MasterData;

use App\Modules\Core\Organization\Entities\BranchEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Repositories\BranchRepositoryInterface;
use App\Modules\Core\Organization\Repositories\BranchRepository;
use App\Modules\Core\Organization\Services\BranchService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class BranchServiceTest extends TestCase
{
    private $repositoryMock;
    private BranchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(BranchRepositoryInterface::class);
        $this->service = new BranchService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_calls_repository_with_normalized_filter(): void
    {
        $filter = BranchEntity::fromArray(['search' => 'clinic', 'search_column' => 'name', 'page' => 1]);

        $this->repositoryMock->shouldReceive('getPageData')->once()
            ->with(Mockery::on(function (BranchEntity $entity) {
                return $entity->search === 'clinic' && $entity->search_column === 'name';
            }))
            ->andReturn(['items' => []]);

        $result = $this->service->index($filter);
        $this->assertCount(0, $result);
    }

    public function test_index_fallbacks_search_column_to_all(): void
    {
        $filter = BranchEntity::fromArray(['search' => 'clinic', 'search_column' => 'invalid_col']);

        $this->repositoryMock->shouldReceive('getPageData')->once()
            ->with(Mockery::on(fn (BranchEntity $entity) => $entity->search_column === 'all'))
            ->andReturn(['items' => []]);

        $this->service->index($filter);
        $this->assertTrue(true);
    }

    public function test_save_throws_exception_if_name_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Nama cabang wajib diisi.');
        $this->service->save(new BranchEntity(name: ''));
    }

    public function test_save_validates_short_name_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Kode cabang maksimal 5 karakter.');
        $this->service->save(new BranchEntity(name: 'Klinik', short_name: 'ABCDEFG'));
    }

    public function test_save_checks_short_name_uniqueness(): void
    {
        $existingBranch = new BranchEntity(uuid: 'uuid-existing', name: 'Existing', short_name: 'KDM');

        $this->repositoryMock->shouldReceive('getByShortName')
            ->with(Mockery::type(BranchEntity::class))
            ->andReturn($existingBranch);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Kode cabang sudah digunakan.');

        $this->service->save(new BranchEntity(uuid: 'uuid-new', name: 'Klinik', short_name: 'KDM'));
    }

    public function test_save_successfully_calls_repository(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn ($closure) => $closure());

        $entity = new BranchEntity(name: 'Klinik Demo', short_name: 'KDM', is_active: true);

        $this->repositoryMock->shouldReceive('getByShortName')->andReturn(null);
        $this->repositoryMock->shouldReceive('save')->once()->with($entity)->andReturn($entity);

        $result = $this->service->save($entity);
        $this->assertSame('KDM', $result->short_name);
    }

    public function test_destroy_returns_error_if_has_warehouses(): void
    {
        $entity = new BranchEntity(uuid: 'uuid-123', actor_uuid: 'actor-uuid');
        $this->repositoryMock->shouldReceive('hasWarehouses')->with($entity->uuid)->andReturn(true);

        $result = $this->service->destroy($entity);

        $this->assertSame('error', $result['status']);
        $this->assertStringContainsString('gudang', $result['message']);
    }

    public function test_destroy_successfully_calls_repository(): void
    {
        $entity = new BranchEntity(uuid: 'uuid-123', actor_uuid: 'actor-123');
        $this->repositoryMock->shouldReceive('hasWarehouses')->with($entity->uuid)->andReturn(false);
        $this->repositoryMock->shouldReceive('delete')->once()->with($entity);

        $result = $this->service->destroy($entity);

        $this->assertSame('success', $result['status']);
    }

    public function test_preview_import_validates_rows(): void
    {
        $rows = [
            ['Kode', 'Nama', 'Desc', 'Active'],
            ['KDM', 'Klinik Demo', 'Desc', 'true'],
            ['', 'Invalid', '', 'true'],
        ];

        $this->repositoryMock->shouldReceive('getByShortName')
            ->with(Mockery::type(BranchEntity::class))
            ->andReturn(null);

        $result = $this->service->previewImport($rows);

        $this->assertCount(1, $result['items']);
        $this->assertSame('new', $result['items'][0]['status']);
    }

    public function test_show_returns_branch_by_uuid(): void
    {
        $branch = Branch::factory()->create();

        $service = new BranchService(new BranchRepository());
        $found = $service->show(BranchEntity::fromArray(['uuid' => $branch->uuid]));

        $this->assertNotNull($found);
        $this->assertSame($branch->uuid, $found->uuid);
    }

    public function test_export_returns_branch_entities(): void
    {
        $branch = Branch::factory()->create();

        $service = new BranchService(new BranchRepository());
        $result = $service->export(BranchEntity::fromArray([
            'company_uuid' => $branch->company_uuid,
        ]));

        $this->assertCount(1, $result);
        $this->assertSame($branch->uuid, $result[0]->uuid);
    }

    public function test_find_first_branch_uuid_returns_first_branch_in_company(): void
    {
        $branch = Branch::factory()->create();

        $service = new BranchService(new BranchRepository());
        $uuid = $service->findFirstBranchUuid($branch->company_uuid);

        $this->assertSame($branch->uuid, $uuid);
    }

    public function test_find_by_uuid_and_company_returns_matching_branch(): void
    {
        $branch = Branch::factory()->create();

        $service = new BranchService(new BranchRepository());
        $found = $service->findByUuidAndCompany($branch->uuid, $branch->company_uuid);

        $this->assertNotNull($found);
        $this->assertSame($branch->uuid, $found->uuid);
    }

    public function test_find_by_uuid_returns_branch(): void
    {
        $branch = Branch::factory()->create();

        $service = new BranchService(new BranchRepository());
        $found = $service->findByUuid($branch->uuid);

        $this->assertNotNull($found);
        $this->assertSame($branch->uuid, $found->uuid);
    }

    public function test_list_branches_returns_branch_entities(): void
    {
        Branch::factory()->count(2)->create();

        $service = new BranchService(new BranchRepository());
        $result = $service->listBranches();

        $this->assertCount(2, $result);
    }

    public function test_get_import_template_returns_branch_structure(): void
    {
        $service = new BranchService(new BranchRepository());
        $template = $service->getImportTemplate();

        $this->assertSame('Data Cabang', $template['sheet']);
        $this->assertContains('Kode Cabang', $template['headers']);
    }

    public function test_import_rows_creates_branches(): void
    {
        $rows = [
            ['Kode', 'Nama', 'Deskripsi', 'Aktif'],
            ['KDM', 'Klinik Demo', 'Cabang pusat', 'true'],
        ];

        $service = new BranchService(new BranchRepository());
        $result = $service->importRows($rows, 'actor-1');

        $this->assertSame(1, $result['total']);
        $this->assertSame(1, $result['success']);
        $this->assertDatabaseHas('branches', [
            'short_name' => 'KDM',
            'name' => 'Klinik Demo',
        ]);
    }
}
