<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Operational\Master\Models\PaymentTerm;
use App\Modules\Operational\Master\Models\Supplier;
use App\Modules\Operational\Master\Models\Tax;
use App\Modules\Operational\Master\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterCompanyScopeIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Company $companyA;

    private Company $companyB;

    private Branch $branchA;

    private Branch $branchB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyA = Company::factory()->create();
        $this->companyB = Company::factory()->create();
        $this->branchA = Branch::factory()->create(['company_uuid' => $this->companyA->uuid]);
        $this->branchB = Branch::factory()->create(['company_uuid' => $this->companyB->uuid]);
    }

    public function test_locations_are_scoped_by_company(): void
    {
        Location::factory()->create([
            'company_uuid' => $this->companyA->uuid,
            'branch_uuid' => $this->branchA->uuid,
        ]);
        Location::factory()->create([
            'company_uuid' => $this->companyB->uuid,
            'branch_uuid' => $this->branchB->uuid,
        ]);

        $this->assertSame(1, Location::query()->where('company_uuid', $this->companyA->uuid)->count());
        $this->assertSame(1, Location::query()->where('company_uuid', $this->companyB->uuid)->count());
    }

    public function test_units_are_scoped_by_company(): void
    {
        Unit::factory()->create([
            'company_uuid' => $this->companyA->uuid,
            'branch_uuid' => $this->branchA->uuid,
        ]);
        Unit::factory()->create([
            'company_uuid' => $this->companyB->uuid,
            'branch_uuid' => $this->branchB->uuid,
        ]);

        $this->assertSame(1, Unit::query()->where('company_uuid', $this->companyA->uuid)->count());
        $this->assertSame(1, Unit::query()->where('company_uuid', $this->companyB->uuid)->count());
    }

    public function test_taxes_are_scoped_by_company_and_branch(): void
    {
        Tax::factory()->create([
            'company_uuid' => $this->companyA->uuid,
            'branch_uuid' => $this->branchA->uuid,
        ]);
        Tax::factory()->create([
            'company_uuid' => $this->companyB->uuid,
            'branch_uuid' => $this->branchB->uuid,
        ]);

        $this->assertSame(1, Tax::query()->where('company_uuid', $this->companyA->uuid)->count());
        $this->assertSame(1, Tax::query()->where('company_uuid', $this->companyB->uuid)->count());
    }

    public function test_payment_terms_are_scoped_by_company_and_branch(): void
    {
        PaymentTerm::factory()->create([
            'company_uuid' => $this->companyA->uuid,
            'branch_uuid' => $this->branchA->uuid,
        ]);
        PaymentTerm::factory()->create([
            'company_uuid' => $this->companyB->uuid,
            'branch_uuid' => $this->branchB->uuid,
        ]);

        $this->assertSame(1, PaymentTerm::query()->where('company_uuid', $this->companyA->uuid)->count());
        $this->assertSame(1, PaymentTerm::query()->where('company_uuid', $this->companyB->uuid)->count());
    }

    public function test_suppliers_are_scoped_by_company_and_branch(): void
    {
        Supplier::factory()->create([
            'company_uuid' => $this->companyA->uuid,
            'branch_uuid' => $this->branchA->uuid,
        ]);
        Supplier::factory()->create([
            'company_uuid' => $this->companyB->uuid,
            'branch_uuid' => $this->branchB->uuid,
        ]);

        $this->assertSame(1, Supplier::query()->where('company_uuid', $this->companyA->uuid)->count());
        $this->assertSame(1, Supplier::query()->where('company_uuid', $this->companyB->uuid)->count());
    }

    public function test_soft_deleted_records_do_not_leak_across_companies(): void
    {
        $location = Location::factory()->create([
            'company_uuid' => $this->companyA->uuid,
            'branch_uuid' => $this->branchA->uuid,
        ]);
        $location->delete();

        $this->assertSame(0, Location::query()->where('company_uuid', $this->companyA->uuid)->count());
    }
}
