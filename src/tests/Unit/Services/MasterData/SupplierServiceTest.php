<?php

namespace Tests\Unit\Services\MasterData;

use App\Modules\Operational\Master\Entities\SupplierEntity;
use App\Modules\Operational\Master\Repositories\SupplierRepository;
use App\Modules\Operational\Master\Services\SupplierService;
use App\Modules\Operational\Master\Models\Supplier;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class SupplierServiceTest extends TestCase
{
    private SupplierService $service;
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(SupplierRepository::class);
        $this->service = new SupplierService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_store_validates_required_fields()
    {
        $this->expectException(ValidationException::class);

        $this->service->store(new SupplierEntity());
    }

    public function test_store_calls_repository_save()
    {
        $entity = new SupplierEntity(
            branch_uuid: 'branch-1',
            company_uuid: 'company-1',
            name: 'Supplier A',
        );

        $this->repository->shouldReceive('save')->once()->with($entity)->andReturn($entity);

        $saved = $this->service->store($entity);

        $this->assertSame('Supplier A', $saved->name);
    }

    public function test_update_calls_repository_save()
    {
        $entity = new SupplierEntity(
            uuid: 'supplier-1',
            branch_uuid: 'branch-1',
            company_uuid: 'company-1',
            name: 'Supplier B',
        );

        $this->repository->shouldReceive('save')->once()->with($entity)->andReturn($entity);

        $updated = $this->service->update($entity);

        $this->assertSame('supplier-1', $updated->uuid);
    }

    public function test_index_returns_repository_payload()
    {
        $entity = new SupplierEntity(
            branch_uuid: 'branch-1',
            company_uuid: 'company-1',
            name: 'Supplier Index'
        );

        $this->repository->shouldReceive('getPageData')->once()->with($entity)->andReturn([
            'items' => [$entity],
            'total' => 1,
        ]);

        $result = $this->service->index($entity);

        $this->assertIsArray($result);
        $this->assertCount(1, $result['items']);
        $this->assertSame(1, $result['total']);
    }

    public function test_destroy_returns_error_if_supplier_has_purchases()
    {
        $entity = new SupplierEntity(uuid: 'supplier-1', branch_uuid: 'branch-1', company_uuid: 'company-1');
        $this->repository->shouldReceive('hasPurchases')->once()->with($entity)->andReturn(true);

        $result = $this->service->destroy($entity);

        $this->assertSame('error', $result['status']);
    }

    public function test_destroy_calls_repository_delete_when_safe()
    {
        $entity = new SupplierEntity(uuid: 'supplier-1', branch_uuid: 'branch-1', company_uuid: 'company-1');
        $this->repository->shouldReceive('hasPurchases')->once()->with($entity)->andReturn(false);
        $this->repository->shouldReceive('delete')->once()->with($entity);

        $result = $this->service->destroy($entity);

        $this->assertSame('success', $result['status']);
    }

    public function test_find_by_uuid_returns_matching_supplier()
    {
        $supplier = Supplier::factory()->create();

        $service = new SupplierService(new SupplierRepository());
        $found = $service->findByUuid(new SupplierEntity(
            uuid: $supplier->uuid,
            branch_uuid: $supplier->branch_uuid,
            company_uuid: $supplier->company_uuid,
        ));

        $this->assertNotNull($found);
        $this->assertSame($supplier->uuid, $found->uuid);
    }
}
