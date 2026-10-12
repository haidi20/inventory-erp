<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Operational\Master\Entities\TreatmentEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Service;
use App\Modules\Operational\Master\Repositories\TreatmentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreatmentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TreatmentRepository $repository;
    private Branch $branch;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TreatmentRepository();
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
    }

    public function test_get_page_data_returns_paginated_services()
    {
        Service::factory()->count(10)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'is_active' => true
        ]);

        $entity = new TreatmentEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            per_page: 5,
            page: 1
        );

        $result = $this->repository->getPageData($entity);

        $this->assertCount(5, $result['items']);
        $this->assertEquals(10, $result['total']);
        $this->assertEquals(10, $result['activeServices']);
        $this->assertInstanceOf(TreatmentEntity::class, $result['items'][0]);
    }

    public function test_save_creates_new_service_and_supports_soft_delete_reentry()
    {
        $code = 'CONS-001';

        // 1. Create and save new service
        $entity = new TreatmentEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: $code,
            name: 'Konsultasi Dokter Umum',
            price: 50000,
            is_active: true
        );

        $saved = $this->repository->save($entity);
        $this->assertDatabaseHas('services', ['uuid' => $saved->uuid, 'code' => $code]);

        // 2. Soft delete the service
        $this->repository->delete($saved);
        
        $deleted = Service::withTrashed()->where('uuid', $saved->uuid)->first();
        $this->assertNotNull($deleted->deleted_at);
        $this->assertNotEquals(0, $deleted->deleted_at);

        // 3. Create NEW service with SAME code (should work due to HasSoftDeleteUnique)
        $newEntity = new TreatmentEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: $code,
            name: 'Konsultasi Spesialis',
            price: 150000
        );

        $reSaved = $this->repository->save($newEntity);
        $this->assertNotNull($reSaved->uuid);
        $this->assertNotEquals($saved->uuid, $reSaved->uuid);
    }

    public function test_save_updates_existing_service()
    {
        $service = Service::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'name' => 'Old Service Name',
            'price' => 10000
        ]);

        $entity = TreatmentEntity::fromModel($service);
        $entity->name = 'New Service Name';
        $entity->price = 15000;

        $updated = $this->repository->save($entity);

        $this->assertEquals('New Service Name', $updated->name);
        $this->assertEquals(15000, $updated->price);
        $this->assertDatabaseHas('services', [
            'uuid' => $service->uuid,
            'name' => 'New Service Name'
        ]);
    }

    public function test_find_by_uuid_returns_correct_service()
    {
        $service = Service::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $found = $this->repository->findByUuid(new TreatmentEntity(
            uuid: $service->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
        ));

        $this->assertNotNull($found);
        $this->assertEquals($service->uuid, $found->uuid);
    }

    public function test_get_page_data_only_returns_services_from_the_same_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);

        Service::factory()->create([
            'code' => 'CONS-SBX',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        Service::factory()->create([
            'code' => 'CONS-OTH',
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $result = $this->repository->getPageData(new TreatmentEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
        ));

        $this->assertCount(1, $result['items']);
        $this->assertSame('CONS-SBX', $result['items'][0]->code);
    }
}
