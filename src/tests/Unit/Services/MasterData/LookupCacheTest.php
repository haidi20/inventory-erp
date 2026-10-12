<?php

namespace Tests\Unit\Services\MasterData;

use App\Modules\Operational\Master\Entities\LookupTypeEntity;
use App\Modules\Operational\Master\Entities\LookupTypeListResultEntity;
use App\Modules\Operational\Master\Entities\LookupValueListResultEntity;
use App\Modules\Operational\Master\Repositories\LookupRepositoryInterface;
use App\Modules\Operational\Master\Services\LookupService;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class LookupCacheTest extends TestCase
{
    private $repository;
    private LookupService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->repository = Mockery::mock(LookupRepositoryInterface::class);
        $this->service = new LookupService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_types_caches_result(): void
    {
        $expected = new LookupTypeListResultEntity(items: [], total: 0, per_page: 20, current_page: 1);

        $this->repository->shouldReceive("getTypes")
            ->once()
            ->andReturn($expected);

        $result1 = $this->service->getTypes();
        $result2 = $this->service->getTypes();

        $this->assertSame($result1, $result2);
        $this->assertTrue(Cache::has("lookup_types"));
    }

    public function test_get_types_calls_repository_only_once(): void
    {
        $expected = new LookupTypeListResultEntity(items: [], total: 0, per_page: 20, current_page: 1);

        $this->repository->shouldReceive("getTypes")
            ->once()
            ->andReturn($expected);

        $this->service->getTypes();
        $this->service->getTypes();
        $this->service->getTypes();

        $this->assertTrue(Cache::has("lookup_types"));
    }

    public function test_get_values_caches_by_type_code(): void
    {
        $entity = new LookupTypeEntity(code: "unit_type", company_uuid: "test-company");
        $expected = new LookupValueListResultEntity(items: [], total: 0, per_page: 20, current_page: 1);

        $this->repository->shouldReceive("getValuesByType")
            ->once()
            ->andReturn($expected);

        $result1 = $this->service->getValues($entity);
        $result2 = $this->service->getValues($entity);

        $this->assertSame($result1, $result2);
        $this->assertTrue(Cache::has("lookup_values_unit_type_test-company"));
    }

    public function test_cache_flush_invalidates_lookup_cache(): void
    {
        $expected = new LookupTypeListResultEntity(items: [], total: 0, per_page: 20, current_page: 1);

        $this->repository->shouldReceive("getTypes")
            ->twice()
            ->andReturn($expected);

        $this->service->getTypes();
        Cache::flush();

        $this->assertFalse(Cache::has("lookup_types"));

        $this->service->getTypes();
    }
}
