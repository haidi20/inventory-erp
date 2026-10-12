<?php

namespace Tests\Feature\Repositories\Operational\Inventory;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Inventory\Entities\BarcodePrintFilterEntity;
use App\Modules\Operational\Inventory\Entities\PriceListFilterEntity;
use App\Modules\Operational\Inventory\Repositories\BarcodePrintRepository;
use App\Modules\Operational\Inventory\Repositories\PriceListRepository;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Service;
use App\Modules\Operational\Master\Models\ServicePackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryListScopeRealDbTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private Company $otherCompany;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->otherCompany = Company::factory()->create();

        Product::factory()->create([
            'branch_uuid' => Branch::factory()->create(['company_uuid' => $this->company->uuid])->uuid,
            'company_uuid' => $this->company->uuid,
            'code' => 'SKU-OWN',
            'name' => 'Own Product',
            'category' => 'Obat',
            'unit' => 'Box',
            'price_buy' => 1000,
            'price_sell' => 1500,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'branch_uuid' => Branch::factory()->create(['company_uuid' => $this->otherCompany->uuid])->uuid,
            'company_uuid' => $this->otherCompany->uuid,
            'code' => 'SKU-OTHER',
            'name' => 'Other Product',
            'is_active' => true,
        ]);

        Service::factory()->create([
            'company_uuid' => $this->company->uuid,
            'code' => 'SVC-OWN',
            'name' => 'Own Service',
        ]);

        Service::factory()->create([
            'company_uuid' => $this->otherCompany->uuid,
            'code' => 'SVC-OTHER',
            'name' => 'Other Service',
        ]);

        ServicePackage::factory()->create([
            'company_uuid' => $this->company->uuid,
            'code' => 'PKG-OWN',
            'name' => 'Own Package',
        ]);

        ServicePackage::factory()->create([
            'company_uuid' => $this->otherCompany->uuid,
            'code' => 'PKG-OTHER',
            'name' => 'Other Package',
        ]);
    }

    public function test_price_list_products_are_scoped_to_company(): void
    {
        $rows = (new PriceListRepository())
            ->getProducts(new PriceListFilterEntity(company_uuid: $this->company->uuid));

        $this->assertSame(['SKU-OWN'], $rows->pluck('code')->all());
        $this->assertSame(500.0, (float) $rows->first()['margin']);
        $this->assertSame('Aktif', $rows->first()['status']);
    }

    public function test_price_list_services_are_scoped_to_company(): void
    {
        $rows = (new PriceListRepository())
            ->getServices(new PriceListFilterEntity(company_uuid: $this->company->uuid));

        $this->assertSame(['SVC-OWN'], $rows->pluck('code')->all());
    }

    public function test_price_list_packages_are_scoped_to_company(): void
    {
        $rows = (new PriceListRepository())
            ->getPackages(new PriceListFilterEntity(company_uuid: $this->company->uuid));

        $this->assertSame(['PKG-OWN'], $rows->pluck('code')->all());
    }

    public function test_barcode_items_are_scoped_company_and_exclude_inactive_or_codeless_items(): void
    {
        Product::factory()->create([
            'company_uuid' => $this->company->uuid,
            'code' => 'SKU-INACTIVE',
            'is_active' => false,
        ]);

        Product::factory()->create([
            'company_uuid' => $this->company->uuid,
            'code' => '',
            'is_active' => true,
        ]);

        $rows = (new BarcodePrintRepository())
            ->getItems(new BarcodePrintFilterEntity(company_uuid: $this->company->uuid));

        $this->assertSame(['SKU-OWN'], $rows->pluck('code')->all());
        $this->assertTrue($rows->first()['low_stock']);
    }
}
