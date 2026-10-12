<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Core\Organization\Entities\CompanyEntity;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Organization\Repositories\CompanyRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CompanyRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CompanyRepository $repository;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CompanyRepository();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_get_index_data_returns_paginated_results()
    {
        Company::factory()->count(15)->create(['deleted_at' => 0]);

        $entity = CompanyEntity::fromArray(['page' => 1, 'per_page' => 10]);
        $result = $this->repository->getPageData($entity);

        $this->assertCount(10, $result['items']);
        $this->assertEquals(15, $result['total']);
    }

    public function test_get_index_data_with_search()
    {
        Company::factory()->create(['name' => 'Company A', 'deleted_at' => 0]);
        Company::factory()->create(['name' => 'Company B', 'deleted_at' => 0]);

        $entity = CompanyEntity::fromArray(['search' => 'Company A', 'page' => 1, 'per_page' => 10]);
        $result = $this->repository->getPageData($entity);

        $this->assertEquals(1, $result['total']);
        $this->assertEquals('Company A', $result['items'][0]->name);
    }

    public function test_get_by_uuid_returns_company()
    {
        $company = Company::factory()->create(['deleted_at' => 0]);

        $found = $this->repository->findByUuid($company->uuid);

        $this->assertNotNull($found);
        $this->assertEquals($company->uuid, $found->uuid);
    }

    public function test_store_creates_new_company()
    {
        $entity = CompanyEntity::fromArray([
            'name' => 'New Company',
            'email' => 'new@company.com',
            'is_active' => true
        ]);

        $company = $this->repository->save($entity);

        $this->assertDatabaseHas('companies', [
            'uuid' => $company->uuid,
            'name' => 'New Company'
        ]);
    }

    public function test_update_modifies_existing_company()
    {
        $company = Company::factory()->create(['name' => 'Old Name']);
        $entity = CompanyEntity::fromArray([
            'uuid' => $company->uuid,
            'name' => 'Updated Name',
            'is_active' => true
        ]);

        $this->repository->save($entity);

        $this->assertDatabaseHas('companies', [
            'uuid' => $company->uuid,
            'name' => 'Updated Name'
        ]);
    }

    public function test_destroy_soft_deletes_company()
    {
        $company = Company::factory()->create();

        $this->repository->delete($company->uuid);

        $this->assertSoftDeleted('companies', ['uuid' => $company->uuid]);
        
        $deletedCompany = Company::withTrashed()->where('uuid', $company->uuid)->first();
        $this->assertNotNull($deletedCompany->deleted_at);
        $deletedAt = $deletedCompany->deleted_at;
        if ($deletedAt instanceof \Illuminate\Support\Carbon) {
            $deletedAt = $deletedAt->timestamp;
        }
        $this->assertGreaterThan(0, (int)$deletedAt);
    }

    public function test_can_recreate_company_with_same_name_after_soft_delete()
    {
        $name = 'UNIQUE COMPANY';
        
        // 1. Create first company
        $company1 = Company::factory()->create([
            'name' => $name
        ]);
        
        // 2. Delete first company
        $this->repository->delete($company1->uuid);
        
        // Verify soft delete
        $c1Deleted = Company::withTrashed()->where('uuid', $company1->uuid)->first();
        $this->assertNotNull($c1Deleted);
        $deletedAt = $c1Deleted->deleted_at;
        if ($deletedAt instanceof \Illuminate\Support\Carbon) {
            $deletedAt = $deletedAt->timestamp;
        }
        $this->assertGreaterThan(0, (int)$deletedAt);
        
        // 3. Create second company with same name
        $entity = CompanyEntity::fromArray([
            'name' => $name,
            'is_active' => true
        ]);
        
        try {
            $company2 = $this->repository->save($entity);
        } catch (\Exception $e) {
            $this->fail("Failed to recreate company: " . $e->getMessage());
        }
        
        $this->assertDatabaseHas('companies', [
            'uuid' => $company2->uuid,
            'name' => $name,
            'deleted_at' => 0
        ]);
    }

    public function test_find_first_active_returns_active_company()
    {
        $company = Company::factory()->create(['is_active' => true]);

        $found = $this->repository->findFirstActive();

        $this->assertNotNull($found);
        $this->assertSame($company->uuid, $found->uuid);
    }
}
