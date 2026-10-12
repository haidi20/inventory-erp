<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Operational\Master\Entities\PersonEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Person;
use App\Modules\Operational\Master\Models\PersonCustomField;
use App\Modules\Operational\Master\Repositories\PersonRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PersonRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PersonRepository $repository;
    private Company $company;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PersonRepository();

        $this->company = Company::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test Company',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'uuid' => (string) Str::uuid(),
            'company_uuid' => $this->company->uuid,
            'short_name' => 'B01',
            'name' => 'Test Branch',
            'is_active' => true,
        ]);
    }

    public function test_get_page_data_returns_paginated_people()
    {
        Person::factory()->count(10)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $entity = new PersonEntity(company_uuid: $this->company->uuid, page: 1, per_page: 5);
        $result = $this->repository->getPageData($entity);

        $this->assertCount(5, $result['items']);
        $this->assertEquals(10, $result['total']);
    }

    public function test_get_page_data_with_search_by_name()
    {
        Person::factory()->create([
            'name' => 'John Doe', 
            'code' => 'P001',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        Person::factory()->create([
            'name' => 'Jane Smith', 
            'code' => 'P002',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $entity = new PersonEntity(company_uuid: $this->company->uuid, search: 'john', page: 1, per_page: 10);
        $result = $this->repository->getPageData($entity);

        $this->assertEquals(1, $result['total']);
        $this->assertEquals('John Doe', $result['items'][0]->name);
    }

    public function test_find_by_uuid_returns_person_with_branch_isolation()
    {
        $person = Person::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $found = $this->repository->findByUuid($person->uuid, $this->branch->uuid, $this->company->uuid);

        $this->assertNotNull($found);
        $this->assertEquals($person->uuid, $found->uuid);

        $notFound = $this->repository->findByUuid($person->uuid, (string) Str::uuid(), $this->company->uuid);
        $this->assertNull($notFound);
    }

    public function test_save_creates_new_person_with_custom_fields()
    {
        $entity = new PersonEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: 'CUST-001',
            name: 'New Customer',
            is_supplier: false,
            custom_fields: [
                ['field_key' => 'Hobby', 'field_value' => 'Coding']
            ]
        );

        $person = $this->repository->save($entity);

        $this->assertDatabaseHas('people', ['uuid' => $person->uuid, 'name' => 'New Customer']);
        $this->assertDatabaseHas('person_custom_fields', [
            'person_uuid' => $person->uuid,
            'field_key' => 'Hobby',
            'field_value' => 'Coding'
        ]);
    }

    public function test_delete_removes_person_and_custom_fields()
    {
        $person = Person::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        PersonCustomField::factory()->create(['person_uuid' => $person->uuid]);

        $entity = new PersonEntity(uuid: $person->uuid);
        $this->repository->delete($entity);

        $this->assertDatabaseMissing('people', [
            'uuid' => $person->uuid,
            'deleted_at' => 0
        ]);
        $this->assertDatabaseMissing('person_custom_fields', [
            'person_uuid' => $person->uuid,
            'deleted_at' => 0
        ]);
    }

    public function test_get_by_code_is_case_insensitive()
    {
        Person::factory()->create([
            'code' => 'CODE123',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $found = $this->repository->getByCode('code123');

        $this->assertNotNull($found);
        $this->assertEquals('CODE123', $found->code);
    }
}
