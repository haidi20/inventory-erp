<?php

namespace Tests\Feature\Repositories\Operational\Master;

use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Entities\CustomerSupplierFilterEntity;
use App\Modules\Operational\Master\Models\Supplier;
use App\Modules\Operational\Master\Repositories\CustomerSupplierRepository;
use App\Modules\Operational\Master\Services\CustomerSupplierService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Test perilaku nyata CustomerSupplierService/Repository dengan entity filter
 * baru -- melengkapi test reflection stub yang sudah ada.
 */
class CustomerSupplierFilterFlowTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected Branch $branch;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create(['deleted_at' => 0]);
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);

        $this->user = User::factory()->create();
        UserCompany::query()->create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->company->uuid,
            'is_default' => true,
        ]);
        UserBranch::query()->create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->company->uuid,
            'branch_uuid' => $this->branch->uuid,
            'is_default' => true,
        ]);

        $this->actingAs($this->user);
        Auth::shouldUse('web');
    }

    private function makePatient(array $overrides = []): Patient
    {
        return Patient::query()->create(array_merge([
            'branch_uuid'      => $this->branch->uuid,
            'company_uuid'     => $this->company->uuid,
            'medical_record_no' => 'RM-001',
            'name'             => 'Andi Pasien',
            'gender'           => 'L',
            'phone'            => '081234567890',
            'address'          => 'Jl. Merdeka 1',
            'bpjs_number'      => null,
            'is_active'        => true,
        ], $overrides));
    }

    private function makeSupplier(array $overrides = []): Supplier
    {
        return Supplier::query()->create(array_merge([
            'branch_uuid'  => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'name'         => 'PT Kimia Farma',
            'phone'        => '0215550001',
            'email'        => 'sales@kimia.co.id',
            'address'      => 'Jl. Industri 2',
            'term_days'    => 30,
            'is_active'    => true,
        ], $overrides));
    }

    public function test_repository_filters_customers_by_search(): void
    {
        $this->makePatient();
        $this->makePatient([
            'medical_record_no' => 'RM-002',
            'name' => 'Budi Antar',
            'phone' => '089900011122',
        ]);

        $repo = new CustomerSupplierRepository();

        $all = $repo->getCustomers(new CustomerSupplierFilterEntity());
        $this->assertCount(2, $all);

        $filtered = $repo->getCustomers(new CustomerSupplierFilterEntity(search: 'Budi'));
        $this->assertCount(1, $filtered);
        $this->assertSame('Budi Antar', $filtered->first()['name']);
    }

    public function test_repository_maps_customer_row_shape(): void
    {
        $this->makePatient([
            'name' => 'Citra BPJS',
            'bpjs_number' => '0001234567',
            'gender' => 'P',
            'phone' => null,
            'address' => null,
        ]);

        $row = (new CustomerSupplierRepository())
            ->getCustomers(new CustomerSupplierFilterEntity())
            ->first();

        $this->assertSame('RM-001', $row['code']);
        $this->assertSame('Citra BPJS', $row['name']);
        $this->assertSame('Perempuan', $row['gender']);
        $this->assertSame('-', $row['phone']);
        $this->assertSame('Terdaftar', $row['bpjs_status']);
        $this->assertSame('-', $row['address']);
    }

    public function test_repository_filters_suppliers_by_search(): void
    {
        $this->makeSupplier();
        $this->makeSupplier(['name' => 'CV Sumber Sehat', 'email' => 'cs@sumbersehat.co.id']);

        $repo = new CustomerSupplierRepository();

        $all = $repo->getSuppliers(new CustomerSupplierFilterEntity());
        $this->assertCount(2, $all);

        $filtered = $repo->getSuppliers(new CustomerSupplierFilterEntity(search: 'sumbersehat'));
        $this->assertCount(1, $filtered);
        $this->assertSame('CV Sumber Sehat', $filtered->first()['name']);
    }

    public function test_service_customers_builds_page_payload(): void
    {
        $this->makePatient();
        $this->makePatient(['medical_record_no' => 'RM-002', 'name' => 'Budi', 'bpjs_number' => '0009998887']);

        $service = new CustomerSupplierService(new CustomerSupplierRepository());
        $page = $service->customers(new CustomerSupplierFilterEntity());

        $this->assertSame('customers', $page['currentTab']);
        $this->assertSame('Customer', $page['pageTitle']);
        $this->assertCount(6, $page['columns']);
        $this->assertCount(2, $page['rows']);
        $this->assertSame('Budi', $page['rows'][1][1]);
        $this->assertSame('Terdaftar', $page['rows'][1][4]);
    }

    public function test_service_suppliers_counts_active_and_avg_term(): void
    {
        $this->makeSupplier(['term_days' => 30]);
        $this->makeSupplier(['name' => 'CV Nonaktif', 'is_active' => false, 'term_days' => 60]);

        $service = new CustomerSupplierService(new CustomerSupplierRepository());
        $page = $service->suppliers(new CustomerSupplierFilterEntity());

        $this->assertSame('suppliers', $page['currentTab']);
        $this->assertCount(2, $page['rows']);
        // Repo orderBy('name'): "CV Nonaktif" sebelum "PT Kimia Farma"
        $this->assertSame('60 hari', $page['rows'][0][3]);
        $this->assertSame('Nonaktif', $page['rows'][0][4]);
        $this->assertSame('30 hari', $page['rows'][1][3]);
        $this->assertSame('Aktif', $page['rows'][1][4]);
    }

    public function test_service_rejects_search_longer_than_100_chars(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $service = new CustomerSupplierService(new CustomerSupplierRepository());
        $service->customers(new CustomerSupplierFilterEntity(search: str_repeat('x', 101)));
    }
}
