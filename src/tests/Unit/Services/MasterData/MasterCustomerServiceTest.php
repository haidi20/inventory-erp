<?php

namespace Tests\Unit\Services\MasterData;

use App\Modules\Operational\Master\Entities\MasterCustomerEntity;
use App\Modules\Operational\Master\Repositories\MasterCustomerRepositoryInterface;
use App\Modules\Operational\Master\Services\MasterCustomerService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class MasterCustomerServiceTest extends TestCase
{
    private $repositoryMock;
    private MasterCustomerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(MasterCustomerRepositoryInterface::class);
        $this->service = new MasterCustomerService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_calls_repository_with_filters(): void
    {
        $filter = MasterCustomerEntity::fromArray(['search' => 'John', 'page' => 2]);
        $this->repositoryMock->shouldReceive('getPageData')->once()
            ->with(Mockery::on(fn (MasterCustomerEntity $entity) => $entity->search === 'John' && $entity->page === 2))
            ->andReturn(['items' => []]);

        $result = $this->service->index($filter);
        $this->assertCount(0, $result);
    }

    public function test_store_validates_and_calls_repository(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($closure) => $closure());

        $entity = new MasterCustomerEntity(name: 'John Doe', email: 'john@example.com');
        $this->repositoryMock->shouldReceive('create')->once()->with($entity)->andReturn($entity);

        $result = $this->service->store($entity);
        $this->assertSame('John Doe', $result->name);
    }

    public function test_store_throws_exception_if_name_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Nama customer wajib diisi.');
        $this->service->store(new MasterCustomerEntity(name: ''));
    }

    public function test_store_throws_exception_if_email_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Format email tidak valid.');
        $this->service->store(new MasterCustomerEntity(name: 'John', email: 'invalid-email'));
    }

    public function test_update_throws_exception_if_uuid_missing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ID customer wajib diisi.');
        $this->service->update(new MasterCustomerEntity(name: 'John'));
    }

    public function test_update_successfully_calls_repository(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($closure) => $closure());

        $entity = new MasterCustomerEntity(uuid: 'uuid-123', name: 'John Update');
        $this->repositoryMock->shouldReceive('update')->once()->with($entity)->andReturn($entity);

        $result = $this->service->update($entity);
        $this->assertSame('uuid-123', $result->uuid);
    }

    public function test_destroy_throws_exception_if_id_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->destroy('');
    }

    public function test_destroy_throws_exception_if_has_transactions(): void
    {
        $uuid = 'uuid-123';
        $this->repositoryMock->shouldReceive('hasTransactions')->with($uuid)->andReturn(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer tidak bisa dihapus karena sudah memiliki riwayat transaksi.');

        $this->service->destroy($uuid);
    }

    public function test_destroy_successfully_calls_repository(): void
    {
        $uuid = 'uuid-123';
        $this->repositoryMock->shouldReceive('hasTransactions')->with($uuid)->andReturn(false);
        $this->repositoryMock->shouldReceive('delete')->once()
            ->with(Mockery::on(fn (MasterCustomerEntity $entity) => $entity->uuid === $uuid));

        $result = $this->service->destroy($uuid);

        $this->assertSame('success', $result['status']);
    }
}
