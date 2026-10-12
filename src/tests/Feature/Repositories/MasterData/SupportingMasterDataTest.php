<?php

namespace Tests\Feature\Repositories\MasterData;

use App\Modules\Core\Organization\Entities\CompanyEntity;
use App\Modules\Operational\Master\Entities\PaymentTermEntity;
use App\Modules\Operational\Master\Entities\PriceLevelEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\PriceLevel;
use App\Modules\Operational\Master\Models\PaymentTerm;
use App\Modules\Core\Organization\Repositories\CompanyRepository;
use App\Modules\Operational\Master\Repositories\PaymentTermRepository;
use App\Modules\Operational\Master\Repositories\PriceLevelRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportingMasterDataTest extends TestCase
{
    use RefreshDatabase;

    private CompanyRepository $companyRepo;
    private PriceLevelRepository $priceLevelRepo;
    private PaymentTermRepository $paymentTermRepo;
    private Branch $branch;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->companyRepo = new CompanyRepository();
        $this->priceLevelRepo = new PriceLevelRepository();
        $this->paymentTermRepo = new PaymentTermRepository();
        
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
    }

    /**
     * Test Company Repository
     */
    public function test_company_repository_crud()
    {
        // 1. Save (Create)
        $entity = new CompanyEntity(
            name: 'Klinik Sehat Utama',
            address: 'Jl. Melati No. 123',
            email: 'info@sehat.com',
            is_active: true
        );

        $saved = $this->companyRepo->save($entity);
        $this->assertDatabaseHas('companies', ['uuid' => $saved->uuid, 'name' => 'Klinik Sehat Utama']);

        // 2. Find
        $found = $this->companyRepo->findByUuid($saved->uuid);
        $this->assertEquals('Klinik Sehat Utama', $found->name);

        // 3. Update
        $found->name = 'Klinik Sehat Global';
        $updated = $this->companyRepo->save($found);
        $this->assertEquals('Klinik Sehat Global', $updated->name);

        // 4. Soft Delete
        $this->companyRepo->delete($updated->uuid);
        $deleted = Company::withTrashed()->where('uuid', $updated->uuid)->first();
        $this->assertNotEquals(0, $deleted->deleted_at);
    }

    /**
     * Test Price Level Repository
     */
    public function test_price_level_repository_crud()
    {
        $entity = new PriceLevelEntity(
            company_uuid: $this->company->uuid,
            branch_uuid: $this->branch->uuid,
            code: 'PLATINUM',
            name: 'Member Platinum',
            description: 'Tingkat harga khusus member loyal',
            is_active: true
        );

        $saved = $this->priceLevelRepo->save($entity);
        $this->assertDatabaseHas('price_levels', ['uuid' => $saved->uuid, 'name' => 'Member Platinum']);

        // Soft Delete Re-entry Support
        $this->priceLevelRepo->delete($saved);
        $deleted = PriceLevel::withTrashed()->where('uuid', $saved->uuid)->first();
        $this->assertNotEquals(0, $deleted->deleted_at);

        $newEntity = new PriceLevelEntity(
            company_uuid: $this->company->uuid,
            branch_uuid: $this->branch->uuid,
            code: 'PLATINUM',
            name: 'Member Platinum'
        );
        $reSaved = $this->priceLevelRepo->save($newEntity);
        $this->assertNotEquals($saved->uuid, $reSaved->uuid);
    }

    /**
     * Test Payment Term Repository
     */
    public function test_payment_term_repository_crud()
    {
        $entity = new PaymentTermEntity(
            company_uuid: $this->company->uuid,
            branch_uuid: $this->branch->uuid,
            name: 'Net 30',
            due_days: 30,
            is_active: true
        );

        $saved = $this->paymentTermRepo->save($entity);
        $this->assertDatabaseHas('payment_terms', ['uuid' => $saved->uuid, 'name' => 'Net 30']);

        // Get Page Data
        $pageResult = $this->paymentTermRepo->getPageData(new PaymentTermEntity(
            company_uuid: $this->company->uuid,
            per_page: 5
        ));
        $this->assertCount(1, $pageResult['items']);
        $this->assertEquals(1, $pageResult['active_terms']);
    }
}
