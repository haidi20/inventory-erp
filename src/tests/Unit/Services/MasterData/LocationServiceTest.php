<?php

namespace Tests\Unit\Services\MasterData;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Entities\LocationEntity;
use App\Modules\Operational\Master\Repositories\LocationRepositoryInterface;
use App\Modules\Operational\Master\Repositories\LocationRepository;
use App\Modules\Core\Organization\Services\BranchService;
use App\Modules\Core\Organization\Repositories\BranchRepository;
use App\Modules\Operational\Master\Services\LocationService;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Operational\Master\Models\Shift;
use Database\Factories\Master\ShiftFactory;
use Tests\TestCase;
use Mockery;

class LocationServiceTest extends TestCase
{
    private $repositoryMock;
    private LocationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(LocationRepositoryInterface::class);
        $this->service = new LocationService($this->repositoryMock, Mockery::mock(BranchService::class));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_calls_repository_with_normalized_filter()
    {
        $filter = LocationEntity::fromArray([
            'search' => 'main',
            'search_column' => 'name',
            'company_uuid' => 'c-1',
        ]);
        $this->repositoryMock->shouldReceive('getPageData')->once()
            ->with(Mockery::on(function (LocationEntity $entity) {
                return $entity->search === 'main' && $entity->search_column === 'name';
            }))
            ->andReturn([
                'items' => [],
                'search' => 'main',
                'search_column' => 'name',
                'branch_uuid' => null,
                'total' => 0,
                'current_page' => 1,
                'per_page' => 10,
                'branches' => [],
                'active_locations' => 0,
            ]);

        $result = $this->service->index($filter);

        $this->assertIsArray($result);
        $this->assertSame('main', $result['search']);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('active_locations', $result);
    }

    public function test_save_validates_required_fields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('cabang gudang wajib diisi');
        $this->service->save(new LocationEntity(branch_uuid: ''));
    }

    public function test_save_checks_duplicate_code_in_branch()
    {
        $existing = new LocationEntity(
            uuid: 'uuid-old',
            branch_uuid: 'b-1',
            company_uuid: 'c-1',
            code: 'GDG1',
            name: 'Gudang Lama',
        );
        
        $this->repositoryMock->shouldReceive('getByBranchAndCode')->andReturn($existing);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('sudah digunakan');
        
        $this->service->save(new LocationEntity(
            branch_uuid: 'b-1',
            code: 'GDG1',
            name: 'Gudang 1',
            uuid: 'uuid-new',
            company_uuid: 'c-1',
        ));
    }

    public function test_destroy_prevents_deletion_if_has_shifts()
    {
        $this->repositoryMock->shouldReceive('hasShifts')->once()->andReturn(true);
        
        $result = $this->service->destroy(new LocationEntity(
            uuid: 'wh-1',
            branch_uuid: 'b-1',
            company_uuid: 'c-1',
        ));
        
        $this->assertEquals('error', $result->status);
        $this->assertStringContainsString('shift', $result->message);
    }

    public function test_destroy_calls_repository_delete()
    {
        $this->repositoryMock->shouldReceive('hasShifts')->once()->andReturn(false);
        $this->repositoryMock->shouldReceive('delete')->once();
        
        $result = $this->service->destroy(new LocationEntity(
            uuid: 'wh-1',
            branch_uuid: 'b-1',
            company_uuid: 'c-1',
        ));
        
        $this->assertEquals('success', $result->status);
    }

    public function test_import_rows_handles_valid_data()
    {
        $rows = [
            ['BranchCode', 'WHCode', 'WHName', 'Desc', 'Active'],
            ['KDM', 'GDG-1', 'Gudang 1', 'Main', 'aktif'],
        ];

        $branch = ['uuid' => 'b-uuid'];

        $this->repositoryMock->shouldReceive('getBranchByShortName')->with('KDM')->andReturn($branch);
        $this->repositoryMock->shouldReceive('getByBranchAndCode')->zeroOrMoreTimes()->andReturn(null);
        $this->repositoryMock->shouldReceive('save')->once()->andReturn(new LocationEntity(
            uuid: 'wh-1',
            branch_uuid: 'b-uuid',
            company_uuid: 'c-1',
            code: 'GDG-1',
            name: 'Gudang 1',
        ));

        $result = $this->service->importRows($rows, 'c-1');

        $this->assertEquals(1, $result['total']);
        $this->assertEquals(1, $result['success']);
    }

    public function test_get_active_locations_returns_only_locations_with_shifts()
    {
        $location = Location::factory()->create(['name' => 'Gudang Aktif']);
        ShiftFactory::new()->create(['location_uuid' => $location->uuid]);
        Location::factory()->create(['name' => 'Gudang Tanpa Shift']);

        $service = new LocationService(new LocationRepository(), new BranchService(new BranchRepository()));
        $result = $service->getActiveLocations();

        $this->assertCount(1, $result);
        $this->assertSame($location->uuid, $result[0]->uuid);
    }

    public function test_get_by_uuid_returns_matching_location()
    {
        $location = Location::factory()->create();

        $service = new LocationService(new LocationRepository(), new BranchService(new BranchRepository()));
        $found = $service->getByUuid(new LocationEntity(
            uuid: $location->uuid,
            branch_uuid: $location->branch_uuid,
            company_uuid: $location->company_uuid,
        ));

        $this->assertNotNull($found);
        $this->assertSame($location->uuid, $found->uuid);
    }

    public function test_export_returns_location_entities()
    {
        $location = Location::factory()->create(['name' => 'Gudang Export']);

        $service = new LocationService(new LocationRepository(), new BranchService(new BranchRepository()));
        $result = $service->export(new LocationEntity(
            company_uuid: $location->company_uuid,
            branch_uuid: $location->branch_uuid,
        ));

        $this->assertCount(1, $result);
        $this->assertSame($location->uuid, $result[0]->uuid);
    }

    public function test_get_import_template_returns_gudang_structure()
    {
        $service = new LocationService(new LocationRepository(), new BranchService(new BranchRepository()));
        $template = $service->getImportTemplate();

        $this->assertSame('Data Gudang', $template['sheet']);
        $this->assertContains('Kode Gudang', $template['headers']);
    }
}
