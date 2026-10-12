<?php

namespace Tests\Feature\MasterData;

use App\Http\Middleware\CheckApiPermission;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Unit;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $company = Company::factory()->create();
        $this->branch = Branch::factory()->create([
            'name' => 'Klinik Test',
            'company_uuid' => $company->uuid,
        ]);
        Unit::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $company->uuid,
            'code' => 'PCS',
            'name' => 'Pcs',
        ]);
        Unit::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $company->uuid,
            'code' => 'TBL',
            'name' => 'Tablet',
        ]);
        $this->withoutMiddleware([Authenticate::class, CheckApiPermission::class]);

        $this->admin = User::factory()->create();
        UserCompany::create([
            'user_uuid' => $this->admin->uuid,
            'company_uuid' => $company->uuid,
            'is_default' => true,
        ]);
        UserBranch::create([
            'user_uuid' => $this->admin->uuid,
            'company_uuid' => $company->uuid,
            'branch_uuid' => $this->branch->uuid,
            'is_default' => true,
        ]);
    }

    public function test_product_page_renders_with_inertia(): void
    {
        $response = $this->actingAs($this->admin)->get('/master/products');

        $response->assertStatus(200);
    }

    public function test_product_api_search_returns_json(): void
    {
        Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'name' => 'Paracetamol',
            'code' => 'PAR-001',
        ]);
        Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'name' => 'Vitamin C',
            'code' => 'VIT-002',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/products?search=Paracetamol');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        $this->assertStringContainsString('Paracetamol', $response->json('data.0.name'));
    }

    public function test_product_store_success(): void
    {
        $payload = [
            'code' => 'OBT-001',
            'name' => 'Obat Batuk',
            'category' => 'Obat Bebas',
            'unit' => 'Botol',
            'price_buy' => 10000,
            'price_sell' => 15000,
            'min_stock' => 5,
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/products', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('items', [
            'code' => 'OBT-001',
            'name' => 'Obat Batuk',
        ]);
    }

    public function test_product_upsert_update_success(): void
    {
        $product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'code' => 'OLD-001',
            'name' => 'Old Name',
        ]);

        $payload = [
            'code' => 'NEW-001',
            'name' => 'New Name',
            'category' => 'Updated Cat',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->putJson('/api/v1/products/'.$product->uuid, $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('items', [
            'uuid' => $product->uuid,
            'code' => 'NEW-001',
            'name' => 'New Name',
        ]);
    }

    public function test_product_delete_soft_deletes_record(): void
    {
        $product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'code' => 'DEL-001',
            'name' => 'Product To Delete',
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson('/api/v1/products/'.$product->uuid);

        $response->assertStatus(204);

        $deleted = DB::table('items')->where('uuid', $product->uuid)->first();
        $this->assertNotNull($deleted);
        $this->assertGreaterThan(0, $deleted->deleted_at);

        $showResponse = $this->actingAs($this->admin)
            ->getJson('/api/v1/products/'.$product->uuid);
        $showResponse->assertStatus(404);
    }

    public function test_product_store_persists_all_form_tabs_by_product_uuid(): void
    {
        $unitUuid = (string) Str::uuid();
        $locationUuid = (string) Str::uuid();
        $priceLevelUuid = (string) Str::uuid();

        $payload = [
            'code' => 'TAB-001',
            'name' => 'Produk Semua Tab',
            'price_buy' => 10000,
            'price_sell' => 15000,
            'coa_sales_uuid' => 'coa-sales-1',
            'coa_inventory_uuid' => 'coa-inventory-1',
            'coa_hpp_uuid' => 'coa-hpp-1',
            'accounts' => [[
                'account_role' => 'sales',
                'account_uuid' => (string) Str::uuid(),
            ], [
                'account_role' => 'inventory',
                'account_uuid' => (string) Str::uuid(),
            ]],
            'units' => [[
                'unit_uuid' => $unitUuid,
                'conversion_ratio' => 12,
                'is_default' => true,
                'is_active' => true,
            ]],
            'opening_balances' => [[
                'location_uuid' => $locationUuid,
                'opening_qty' => 25,
                'period' => '2026-09',
            ]],
            'prices' => [[
                'price_level_uuid' => $priceLevelUuid,
                'price_buy' => 10000,
                'price_sell' => 15000,
                'discount_amount' => 500,
                'discount_percent' => 0,
                'base_qty' => 1,
                'is_active' => true,
            ]],
            'discounts' => [[
                'quantity' => 10,
                'discount_percent' => 5,
                'discount_amount' => 0,
                'base_qty' => 1,
            ]],
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/products', $payload);

        $response->assertStatus(200);
        $productUuid = $response->json('data.uuid');

        $this->assertNotEmpty($productUuid);
        $this->assertDatabaseHas('items', ['uuid' => $productUuid, 'code' => 'TAB-001']);
        $this->assertDatabaseHas('item_accounts', ['item_uuid' => $productUuid, 'account_role' => 'sales', 'account_uuid' => $payload['accounts'][0]['account_uuid']]);
        $this->assertDatabaseHas('item_accounts', ['item_uuid' => $productUuid, 'account_role' => 'inventory', 'account_uuid' => $payload['accounts'][1]['account_uuid']]);
        $this->assertDatabaseHas('item_units', ['item_uuid' => $productUuid, 'unit_uuid' => $unitUuid, 'conversion_ratio' => 12]);
        $this->assertDatabaseHas('item_opening_balances', ['item_uuid' => $productUuid, 'location_uuid' => $locationUuid, 'opening_qty' => 25, 'period_month' => '2026-09']);
        $this->assertDatabaseHas('item_prices', ['item_uuid' => $productUuid, 'price_level_uuid' => $priceLevelUuid, 'price' => 15000]);
        $this->assertDatabaseHas('item_discount_rules', ['item_uuid' => $productUuid, 'min_qty' => 10, 'amount' => 5, 'discount_type' => 'percentage']);
    }

    public function test_product_upsert_unique_code_validation_ignores_self(): void
    {
        $product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'code' => 'SAME-001',
            'name' => 'Original Name',
        ]);

        $payload = [
            'code' => 'SAME-001',
            'name' => 'Updated Name',
        ];

        $response = $this->actingAs($this->admin)
            ->putJson('/api/v1/products/'.$product->uuid, $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('items', ['uuid' => $product->uuid, 'name' => 'Updated Name']);
    }

    public function test_product_export_route(): void
    {
        Product::factory()->create(['branch_uuid' => $this->branch->uuid, 'company_uuid' => $this->branch->company_uuid, 'code' => 'EXP-001', 'name' => 'Export Product']);
        $response = $this->actingAs($this->admin)->get('/api/v1/products/export?search=EXP-001');
        $response->assertStatus(200)->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('EXP-001', $response->streamedContent());
    }

    public function test_product_export_applies_code_filter(): void
    {
        Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'code' => 'FILTER-EXPORT-A',
            'name' => 'Filtered Export Product',
        ]);
        Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'code' => 'OTHER-EXPORT-B',
            'name' => 'Other Export Product',
        ]);

        $content = $this->actingAs($this->admin)
            ->get('/api/v1/products/export?filter_code=FILTER-EXPORT')
            ->streamedContent();

        $this->assertStringContainsString('FILTER-EXPORT-A', $content);
        $this->assertStringNotContainsString('OTHER-EXPORT-B', $content);
    }

    public function test_product_export_applies_barcode_filter(): void
    {
        Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'code' => 'BARCODE-MATCH',
            'name' => 'Barcode Match',
            'barcode' => '8991234567890',
        ]);
        Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'code' => 'BARCODE-OTHER',
            'name' => 'Barcode Other',
            'barcode' => '8999876543210',
        ]);

        $content = $this->actingAs($this->admin)
            ->get('/api/v1/products/export?filter_barcode=8991234567890')
            ->streamedContent();

        $this->assertStringContainsString('BARCODE-MATCH', $content);
        $this->assertStringNotContainsString('BARCODE-OTHER', $content);
    }

    public function test_product_download_template(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/api/v1/products/import/template');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertHeader('content-disposition', 'attachment; filename=products-import-template.csv');
        $this->assertStringContainsString('code,name,barcode', $response->streamedContent());
    }

    public function test_product_import_success(): void
    {
        $content = "code,name,category,unit,price_buy,price_sell,min_stock\n"
            ."IMP-001,Imported Product,Medicine,Tablet,1000,1500,20\n";
        $file = File::createWithContent('products.csv', $content);

        $response = $this->actingAs($this->admin)
            ->post('/api/v1/products/import', ['file' => $file]);

        $response->assertStatus(200)
            ->assertJsonPath('imported', 1)
            ->assertJsonPath('updated', 0);
        $this->assertDatabaseHas('items', [
            'code' => 'IMP-001',
            'name' => 'Imported Product',
        ]);
    }

    public function test_product_import_upsert_by_code(): void
    {
        $product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'code' => 'EXIST-001',
            'name' => 'Original Product',
        ]);
        $content = "code,name,category,unit,price_buy,price_sell,min_stock\n"
            ."EXIST-001,Updated Product Name,New Category,Pcs,5000,7000,10\n";
        $file = File::createWithContent('products_upsert.csv', $content);

        $response = $this->actingAs($this->admin)
            ->post('/api/v1/products/import', ['file' => $file]);

        $response->assertStatus(200)
            ->assertJsonPath('imported', 0)
            ->assertJsonPath('updated', 1);
        $this->assertDatabaseHas('items', [
            'uuid' => $product->uuid,
            'code' => 'EXIST-001',
            'name' => 'Updated Product Name',
        ]);
    }

    public function test_product_import_reports_invalid_rows_without_stopping_valid_rows(): void
    {
        $content = "code,name,category,unit,price_buy,price_sell,min_stock\n"
            .",Missing Code,Medicine,Tablet,1000,1500,20\n"
            ."IMPORT-VALID,Valid Imported Product,Medicine,Tablet,1000,1500,20\n";
        $file = File::createWithContent('products_partial.csv', $content);

        $response = $this->actingAs($this->admin)
            ->post('/api/v1/products/import', ['file' => $file]);

        $response->assertStatus(200)
            ->assertJsonPath('imported', 1)
            ->assertJsonPath('updated', 0)
            ->assertJsonPath('errors.0', 'Baris 2: code dan name wajib diisi.');
        $this->assertDatabaseHas('items', ['code' => 'IMPORT-VALID']);
    }
}
