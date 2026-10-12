<?php

namespace Tests\Feature\Repositories\Operational;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Operational\Master\Models\Shift;
use App\Modules\Operational\Sales\Repositories\ShipRepository;
use Illuminate\Support\Str;

class ShipRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ShipRepository $repository;
    private Company $companyA;
    private Company $companyB;
    private Branch $branchA;
    private Branch $branchB;
    private Location $warehouseA;
    private Location $warehouseB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(ShipRepository::class);

        // Create companies
        $this->companyA = Company::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Company A',
            'clinic_type' => 'Klinik',
            'phone' => '0411000001',
            'email' => 'company-a@test.local',
            'address' => 'Street A',
            'city' => 'Makassar',
            'is_active' => true,
        ]);

        $this->companyB = Company::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Company B',
            'clinic_type' => 'Klinik',
            'phone' => '0411000002',
            'email' => 'company-b@test.local',
            'address' => 'Street B',
            'city' => 'Makassar',
            'is_active' => true,
        ]);

        // Create branches
        $this->branchA = Branch::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Branch A',
            'short_name' => 'BA',
            'company_uuid' => $this->companyA->uuid,
            'is_active' => true,
        ]);

        $this->branchB = Branch::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Branch B',
            'short_name' => 'BB',
            'company_uuid' => $this->companyB->uuid,
            'is_active' => true,
        ]);

        // Create warehouses
        $this->warehouseA = Location::create([
            'uuid' => (string) Str::uuid(),
            'code' => 'WH-A',
            'name' => 'Warehouse A',
            'branch_uuid' => $this->branchA->uuid,
            'company_uuid' => $this->companyA->uuid,
            'is_active' => true,
        ]);

        $this->warehouseB = Location::create([
            'uuid' => (string) Str::uuid(),
            'code' => 'WH-B',
            'name' => 'Warehouse B',
            'branch_uuid' => $this->branchB->uuid,
            'company_uuid' => $this->companyB->uuid,
            'is_active' => true,
        ]);
    }

    public function test_get_open_shift_respects_company_scope()
    {
        // Create shifts for each company
        Shift::create([
            'shift_number' => 1,
            'date' => now()->timestamp,
            'start_time' => now()->timestamp,
            'opening_balance' => 0,
            'location_uuid' => $this->warehouseA->uuid,
            'branch_uuid' => $this->branchA->uuid,
            'company_uuid' => $this->companyA->uuid,
            'user_uuid' => $this->uuid(),
            'is_closed' => false,
        ]);

        Shift::create([
            'shift_number' => 1,
            'date' => now()->timestamp,
            'start_time' => now()->timestamp,
            'opening_balance' => 0,
            'location_uuid' => $this->warehouseB->uuid,
            'branch_uuid' => $this->branchB->uuid,
            'company_uuid' => $this->companyB->uuid,
            'user_uuid' => $this->uuid(),
            'is_closed' => false,
        ]);

        // Get open shift for company A warehouse
        $shiftA = $this->repository->getOpenShift($this->warehouseA->uuid);

        // Should get company A's shift
        $this->assertNotNull($shiftA);
        $this->assertEquals($this->companyA->uuid, $shiftA->company_uuid);
    }

    public function test_company_isolation_on_get_open_shift()
    {
        $shiftA = Shift::create([
            'shift_number' => 1,
            'date' => now()->timestamp,
            'start_time' => now()->timestamp,
            'opening_balance' => 0,
            'location_uuid' => $this->warehouseA->uuid,
            'branch_uuid' => $this->branchA->uuid,
            'company_uuid' => $this->companyA->uuid,
            'user_uuid' => $this->uuid(),
            'is_closed' => false,
        ]);

        $shiftB = Shift::create([
            'shift_number' => 1,
            'date' => now()->timestamp,
            'start_time' => now()->timestamp,
            'opening_balance' => 0,
            'location_uuid' => $this->warehouseB->uuid,
            'branch_uuid' => $this->branchB->uuid,
            'company_uuid' => $this->companyB->uuid,
            'user_uuid' => $this->uuid(),
            'is_closed' => false,
        ]);

        // Company A should only access warehouse A
        $result = $this->repository->getOpenShift($this->warehouseA->uuid);
        $this->assertNotNull($result);
        $this->assertEquals($shiftA->id, $result->id);
    }

    private function uuid(): string
    {
        return (string) Str::uuid();
    }
}
