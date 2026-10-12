<?php

namespace Tests\Feature\Operational;

use App\Modules\Operational\Sales\Entities\SaleItemEntity;
use App\Modules\Operational\Sales\Entities\SaleTransactionEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Operational\Sales\Models\Sale;
use App\Modules\Operational\Sales\Repositories\TransactionRepository;
use Tests\Traits\ManualDatabaseSetup;
use Illuminate\Support\Str;
use Tests\Concerns\InteractsWithSandboxRuntime;
use Tests\TestCase;

class TransactionRepositoryIsolationTest extends TestCase
{
    use ManualDatabaseSetup, InteractsWithSandboxRuntime;

    private TransactionRepository $repo;
    private Company $companyA;
    private Branch $branchA;
    private User $userA;
    private Company $companyB;
    private Branch $branchB;
    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        config(['permission.cache.store' => 'array']);

        $this->repo = app(TransactionRepository::class);

        // Company A = sandbox
        $this->companyA = $this->sandboxCompany();
        $this->branchA  = $this->sandboxBranch();
        $this->userA    = User::factory()->create([
            'branch_uuid'  => $this->branchA->uuid,
            'company_uuid' => $this->companyA->uuid,
        ]);

        // Company B = other tenant
        $this->companyB = Company::factory()->create();
        $this->branchB  = Branch::factory()->create(['company_uuid' => $this->companyB->uuid]);
        $this->userB    = User::factory()->create([
            'branch_uuid'  => $this->branchB->uuid,
            'company_uuid' => $this->companyB->uuid,
        ]);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function makeSale(Branch $branch, Company $company): Sale
    {
        return Sale::query()->create([
            'uuid'           => (string) Str::uuid(),
            'branch_uuid'    => $branch->uuid,
            'company_uuid'   => $company->uuid,
            'sale_no'        => 'POS-TEST-' . Str::random(6),
            'total_amount'   => 10000,
            'paid'           => 10000,
            'change'         => 0,
            'payment_status' => 'paid',
            'sale_at'        => time(),
            'deleted_at'     => 0,  // HasSoftDeleteUnique: 0 = aktif
        ]);
    }

    private function makeProduct(Branch $branch, Company $company): Product
    {
        return Product::factory()->create([
            'branch_uuid'     => $branch->uuid,
            'company_uuid'    => $company->uuid,
            'base_price_sell' => 10000,
            'is_active'       => true,
        ]);
    }

    private function makePatient(Branch $branch, Company $company): Patient
    {
        return Patient::factory()->create([
            'branch_uuid'  => $branch->uuid,
            'company_uuid' => $company->uuid,
            'is_active'    => true,
        ]);
    }

    private function makeCashier(Branch $branch, Company $company): Employee
    {
        return Employee::factory()->create([
            'branch_uuid'  => $branch->uuid,
            'company_uuid' => $company->uuid,
            'role'         => 'kasir',
            'is_active'    => true,
        ]);
    }

    // -----------------------------------------------------------------------
    // Test 1: getSalePageData() hanya tampil data company yang sama
    // -----------------------------------------------------------------------

    public function test_get_sale_page_data_hanya_tampil_patient_dan_product_company_sendiri(): void
    {
        $patientA = $this->makePatient($this->branchA, $this->companyA);
        $productA = $this->makeProduct($this->branchA, $this->companyA);
        $cashierA = $this->makeCashier($this->branchA, $this->companyA);

        // Data company B — tidak boleh muncul
        $patientB = $this->makePatient($this->branchB, $this->companyB);
        $productB = $this->makeProduct($this->branchB, $this->companyB);
        $cashierB = $this->makeCashier($this->branchB, $this->companyB);


        $this->actingAs($this->userA);

        $data = $this->repo->getSalePageData();

        $patientUuids = collect($data['patients'])->pluck('uuid')->toArray();
        $productUuids = collect($data['products'])->pluck('uuid')->toArray();
        $cashierUuids = collect($data['cashiers'])->pluck('uuid')->toArray();

        $this->assertContains($patientA->uuid, $patientUuids, 'Patient company A harus muncul');
        $this->assertContains($productA->uuid, $productUuids, 'Product company A harus muncul');
        $this->assertContains($cashierA->uuid, $cashierUuids, 'Cashier company A harus muncul');

        $this->assertNotContains($patientB->uuid, $patientUuids, 'Patient company B tidak boleh muncul');
        $this->assertNotContains($productB->uuid, $productUuids, 'Product company B tidak boleh muncul');
        $this->assertNotContains($cashierB->uuid, $cashierUuids, 'Cashier company B tidak boleh muncul');
    }

    // -----------------------------------------------------------------------
    // Test 2: createSale() menyimpan company_uuid dari context user
    // -----------------------------------------------------------------------

    public function test_create_sale_menyimpan_company_uuid_dari_user_yang_login(): void
    {
        $product = $this->makeProduct($this->branchA, $this->companyA, 10);

        $this->actingAs($this->userA);

        $entity = new SaleTransactionEntity(
            patient_uuid: null,
            cashier_uuid: null,
            paid: 10000,
            items: [
                new SaleItemEntity(product_uuid: $product->uuid, qty: 1),
            ]
        );

        $saleUuid = $this->repo->createSale($entity);

        $this->assertDatabaseHas('sales', [
            'uuid'         => $saleUuid,
            'company_uuid' => $this->companyA->uuid,
            'branch_uuid'  => $this->branchA->uuid,
        ]);
    }

    // -----------------------------------------------------------------------
    // Test 3: getSaleDetail() tidak bisa ambil sale milik company lain
    // -----------------------------------------------------------------------

    public function test_get_sale_detail_menolak_sale_milik_company_lain(): void
    {
        $saleB = $this->makeSale($this->branchB, $this->companyB);

        $this->actingAs($this->userA);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repo->getSaleDetail($saleB->uuid);
    }

    // -----------------------------------------------------------------------
    // Test 4: getSaleDetail() berhasil ambil sale milik company sendiri
    // -----------------------------------------------------------------------

    public function test_get_sale_detail_berhasil_ambil_sale_company_sendiri(): void
    {
        $saleA = $this->makeSale($this->branchA, $this->companyA);

        $this->actingAs($this->userA);

        $result = $this->repo->getSaleDetail($saleA->uuid);

        $this->assertSame($saleA->uuid, $result->uuid);
    }

    // -----------------------------------------------------------------------
    // Test 5: getSalesPageData() hanya tampil sale company sendiri
    // -----------------------------------------------------------------------

    public function test_get_sales_page_data_hanya_tampil_sale_company_sendiri(): void
    {
        $saleA = $this->makeSale($this->branchA, $this->companyA);
        $saleB = $this->makeSale($this->branchB, $this->companyB);

        $this->actingAs($this->userA);

        $data      = $this->repo->getSalesPageData();
        $paginator = $data['sales'];
        // LengthAwarePaginator — ambil items via getCollection()
        $saleUuids = $paginator->getCollection()->pluck('uuid')->toArray();

        $this->assertContains($saleA->uuid, $saleUuids, 'Sale company A harus muncul');
        $this->assertNotContains($saleB->uuid, $saleUuids, 'Sale company B tidak boleh muncul');
    }
}
