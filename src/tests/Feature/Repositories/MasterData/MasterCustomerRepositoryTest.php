<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Operational\Master\Entities\MasterCustomerEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Person;
use App\Modules\Operational\Master\Models\PersonAddress;
use App\Modules\Operational\Master\Models\PersonContact;
use App\Modules\Operational\Master\Repositories\MasterCustomerRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MasterCustomerRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private MasterCustomerRepository $repository;
    private Branch $branch;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new MasterCustomerRepository();

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

    public function test_get_page_data_returns_only_customers()
    {
        Person::factory()->count(5)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'is_supplier' => false
        ]);
        Person::factory()->count(3)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'is_supplier' => true
        ]);

        $entity = new MasterCustomerEntity(
            company_uuid: $this->company->uuid,
            page: 1, 
            per_page: 10
        );
        $result = $this->repository->getPageData($entity);

        $this->assertEquals(5, $result['total']);
        $this->assertCount(5, $result['items']);
    }

    public function test_save_creates_customer_with_addresses_and_contacts()
    {
        $entity = new MasterCustomerEntity(
            name: 'Customer Test',
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            is_active: true,
            addresses: [
                ['type' => 'Billing', 'address1' => 'Street 1']
            ],
            contacts: [
                ['name' => 'John', 'phone' => '123']
            ]
        );

        $person = $this->repository->create($entity);

        $this->assertDatabaseHas('people', ['uuid' => $person->uuid, 'name' => 'Customer Test']);
        $this->assertCount(1, $person->addresses);
        $this->assertCount(1, $person->contacts);
        $this->assertEquals('Street 1', $person->addresses[0]['address1']);
    }

    public function test_save_updates_customer_and_syncs_relations()
    {
        $person = Person::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'name' => 'Old Name'
        ]);
        $address = PersonAddress::factory()->create(['person_uuid' => $person->uuid, 'address1' => 'Old Address']);

        $entity = new MasterCustomerEntity(
            uuid: $person->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            name: 'New Name',
            is_active: true,
            addresses: [
                ['uuid' => $address->uuid, 'type' => 'Office', 'address1' => 'New Address']
            ],
            contacts: []
        );

        $result = $this->repository->update($entity);

        $this->assertEquals('New Name', $result->name);
        $this->assertCount(1, $result->addresses);
        $this->assertEquals('New Address', $result->addresses[0]['address1']);
    }

    public function test_has_transactions_returns_true_if_linked_to_sales()
    {
        $person = Person::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'is_supplier' => false
        ]);
        
        $this->assertFalse($this->repository->hasTransactions($person->uuid));
    }

    public function test_delete_removes_customer()
    {
        $person = Person::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $entity = new MasterCustomerEntity(uuid: $person->uuid);
        $this->repository->delete($entity);

        $this->assertSoftDeleted('people', ['uuid' => $person->uuid]);
    }
}
