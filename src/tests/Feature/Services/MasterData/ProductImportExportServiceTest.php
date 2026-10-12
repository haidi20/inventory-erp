<?php

namespace Tests\Feature\Services\MasterData;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Entities\ProductEntity;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Unit;
use App\Modules\Operational\Master\Services\ProductExportService;
use App\Modules\Operational\Master\Services\ProductImportService;
use App\Modules\Operational\Master\Support\ProductFilter;
use App\Modules\Operational\Master\Support\ProductFilterBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ProductImportExportServiceTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        Unit::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'code' => 'PCS',
            'name' => 'Pcs',
        ]);
    }

    public function test_template_and_roundtrip_import_then_export(): void
    {
        $importService = app(ProductImportService::class);
        $template = TestResponse::fromBaseResponse($importService->template())->streamedContent();
        $this->assertStringStartsWith('code,name,barcode', $template);

        $csv = "code,name,barcode,category,unit,price_buy,price_sell,min_stock,max_stock,max_order,is_active\n"
            ."RT-001,Roundtrip Product,899000000001,Obat,Pcs,1000,1500,5,20,30,1\n";
        $result = $importService->import(UploadedFile::fake()->createWithContent('products.csv', $csv), $this->branch->uuid, $this->company->uuid);

        $this->assertSame([], $result['errors']);
        $this->assertSame(1, $result['imported']);
        $this->assertSame(0, $result['updated']);

        $exportResponse = app(ProductExportService::class)->stream(new ProductEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
        ));
        $export = TestResponse::fromBaseResponse($exportResponse)->streamedContent();

        $this->assertStringContainsString('RT-001,"Roundtrip Product",899000000001', $export);
        $this->assertStringContainsString('1000,1500,5,20,30,1', $export);
    }

    public function test_import_rejects_wrong_header(): void
    {
        $result = $this->import('nama,kode\nA,B');

        $this->assertSame(0, $result['imported']);
        $this->assertSame(['Header CSV wajib memuat code dan name.'], $result['errors']);
    }

    public function test_import_rejects_empty_file(): void
    {
        $result = $this->import('');

        $this->assertSame(['File CSV kosong atau tidak valid.'], $result['errors']);
    }

    public function test_import_reports_missing_unit(): void
    {
        $result = $this->import("code,name,unit\nMISS-UNIT,Missing Unit,Sachet");

        $this->assertSame(0, $result['imported']);
        $this->assertSame(['Baris 2: Unit tidak ditemukan.'], $result['errors']);
    }

    public function test_import_reports_duplicate_code_and_barcode(): void
    {
        $result = $this->import(
            "code,name,barcode\nDUP-001,First Product,BAR-1\nDUP-001,Second Product,BAR-2\nDUP-002,Third Product,BAR-1"
        );

        $this->assertSame(1, $result['imported']);
        $this->assertContains('Baris 3: Kode produk sudah digunakan.', $result['errors']);
        $this->assertContains('Baris 4: Barcode sudah digunakan.', $result['errors']);
    }

    public function test_import_reads_utf8_bom_and_windows_1252_encoding(): void
    {
        $csv = "\xEF\xBB\xBFcode,name\nBOM-001,Café Product\n";
        $result = $this->import($csv);

        $this->assertSame([], $result['errors']);
        $this->assertDatabaseHas('items', ['code' => 'BOM-001', 'name' => 'Café Product']);
    }

    public function test_export_and_page_share_filter_builder(): void
    {
        Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'code' => 'FILTER-ONE',
            'name' => 'One Product',
        ]);
        Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'code' => 'OTHER-TWO',
            'name' => 'Other Product',
        ]);

        $entity = new ProductEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            filter_code: 'FILTER-',
        );
        $query = Product::query();
        (new ProductFilterBuilder)->apply($query, ProductFilter::fromEntity($entity));

        $this->assertSame(1, $query->count());
    }

    private function import(string $content): array
    {
        return app(ProductImportService::class)->import(
            UploadedFile::fake()->createWithContent('products.csv', $content),
            $this->branch->uuid,
            $this->company->uuid
        );
    }
}
