<?php

namespace Tests\Unit\Services\MasterData;

use App\Modules\Operational\Master\Entities\ProductEntity;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\ProductImage;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Repositories\ProductRepository;
use App\Modules\Core\Organization\Services\BranchService;
use App\Modules\Core\Organization\Services\CompanyService;
use App\Modules\Operational\Master\Services\ProductService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    private ProductService $service;
    private ProductRepository $repository;
    private Branch $branch;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProductRepository();
        $this->service = new ProductService(
            $this->repository,
            app(BranchService::class),
            app(CompanyService::class)
        );

        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
    }

    public function test_save_validates_required_fields()
    {
        $this->expectException(ValidationException::class);

        // Missing branch_uuid/company_uuid/code/name
        $entity = new ProductEntity();
        $this->service->save($entity);
    }

    public function test_index_returns_repository_payload()
    {
        Product::factory()->count(2)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $result = $this->service->index(new ProductEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            per_page: 10
        ));

        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertCount(2, $result['items']);
    }

    public function test_save_checks_duplicate_code_in_branch()
    {
        Product::factory()->create([
            'code' => 'OBT01',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid
        ]);

        $this->expectException(ValidationException::class);

        $entity = new ProductEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: 'OBT01',
            name: 'New Medicine'
        );
        $this->service->save($entity);
    }

    public function test_save_rejects_unsupported_file()
    {
        Storage::fake('public');

        $entity = new ProductEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: 'OBT02',
            name: 'New Medicine'
        );
        $file = UploadedFile::fake()->createWithContent('payload.php', '<?php echo 1;');

        $this->expectException(ValidationException::class);

        $this->service->save($entity, null, [$file]);
    }

    public function test_save_accepts_pdf_file()
    {
        Storage::fake('public');
        $repository = $this->createMock(ProductRepository::class);
        $repository->expects($this->once())
            ->method('save')
            ->willReturnArgument(0);

        $entity = new ProductEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: 'OBT07',
            name: 'New Medicine'
        );
        $file = UploadedFile::fake()->createWithContent('document.pdf', "%PDF-1.4\n1 0 obj\nendobj\n%%EOF");

        $service = new ProductService(
            $repository,
            app(BranchService::class),
            app(CompanyService::class)
        );

        $result = $service->save($entity, null, [$file]);

        $this->assertStringEndsWith('.pdf', $result->image);
        Storage::disk('public')->assertExists($result->image);
    }

    public function test_save_accepts_image_file()
    {
        Storage::fake('public');
        $repository = $this->createMock(ProductRepository::class);
        $repository->expects($this->once())
            ->method('save')
            ->willReturnArgument(0);

        $entity = new ProductEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: 'OBT03',
            name: 'New Medicine'
        );
        $file = UploadedFile::fake()->image('product.png');

        $service = new ProductService(
            $repository,
            app(BranchService::class),
            app(CompanyService::class)
        );

        $result = $service->save($entity, $file);

        $this->assertStringStartsWith('items/', $result->image);
        Storage::disk('public')->assertExists($result->image);
    }

    public function test_save_accepts_multiple_image_files()
    {
        Storage::fake('public');
        $repository = $this->createMock(ProductRepository::class);
        $repository->expects($this->once())
            ->method('save')
            ->willReturnArgument(0);

        $entity = new ProductEntity(
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: 'OBT04',
            name: 'New Medicine'
        );
        $images = [
            UploadedFile::fake()->image('product-1.png'),
            UploadedFile::fake()->image('product-2.png'),
        ];

        $service = new ProductService(
            $repository,
            app(BranchService::class),
            app(CompanyService::class)
        );

        $result = $service->save($entity, null, $images);

        $this->assertSame($result->images[0], $result->image);
        $this->assertCount(2, $result->images);
        Storage::disk('public')->assertExists($result->images[0]);
        Storage::disk('public')->assertExists($result->images[1]);
    }

    public function test_save_sets_next_image_as_primary_after_first_removed()
    {
        $repository = $this->createMock(ProductRepository::class);
        $ownedProduct = new ProductEntity(
            uuid: '0b9c5f46-4a39-4b8f-9e0b-52f9b1d5d101',
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: 'OBT05',
            name: 'New Medicine',
            images: ['items/primary.png', 'items/secondary.png'],
        );
        $repository->method('findByUuid')
            ->willReturn($ownedProduct);
        $repository->expects($this->exactly(2))
            ->method('save')
            ->willReturnArgument(0);

        $entity = new ProductEntity(
            uuid: '0b9c5f46-4a39-4b8f-9e0b-52f9b1d5d101',
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: 'OBT05',
            name: 'New Medicine'
        );

        $service = new ProductService(
            $repository,
            app(BranchService::class),
            app(CompanyService::class)
        );

        $result = $service->save($entity, null, [], ['items/primary.png', 'items/secondary.png']);
        $this->assertSame('items/primary.png', $result->image);

        $result = $service->save($entity, null, [], ['items/secondary.png']);
        $this->assertSame('items/secondary.png', $result->image);
    }

    public function test_save_rejects_more_than_ten_total_images()
    {
        Storage::fake('public');
        $product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $existingImagePaths = array_map(
            fn (int $index) => "items/existing-{$index}.png",
            range(1, 10)
        );
        foreach ($existingImagePaths as $index => $existingImagePath) {
            ProductImage::query()->create([
                'item_uuid' => $product->uuid,
                'branch_uuid' => $this->branch->uuid,
                'company_uuid' => $this->company->uuid,
                'path' => $existingImagePath,
                'sort_order' => $index,
            ]);
        }

        $entity = new ProductEntity(
            uuid: $product->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: $product->code,
            name: $product->name
        );

        $this->expectException(ValidationException::class);

        $this->service->save($entity, null, [UploadedFile::fake()->image('replacement.png')], $existingImagePaths);
    }

    public function test_save_rejects_existing_file_path_owned_by_other_product()
    {
        $product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $otherProduct = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        ProductImage::query()->create([
            'item_uuid' => $otherProduct->uuid,
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'path' => 'items/other-product.png',
            'sort_order' => 0,
        ]);

        $entity = new ProductEntity(
            uuid: $product->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: 'OBT08',
            name: 'New Medicine'
        );

        $this->expectException(ValidationException::class);

        $this->service->save($entity, null, [], ['items/other-product.png']);
    }

    public function test_save_deletes_replaced_product_file_from_storage()
    {
        Storage::fake('public');
        Storage::disk('public')->put('items/replaced.png', 'old-file');
        $product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        ProductImage::query()->create([
            'item_uuid' => $product->uuid,
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'path' => 'items/replaced.png',
            'sort_order' => 0,
        ]);

        $entity = new ProductEntity(
            uuid: $product->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid,
            code: $product->code,
            name: $product->name
        );
        $replacement = UploadedFile::fake()->image('replacement.png');

        $result = $this->service->save($entity, null, [$replacement]);

        Storage::disk('public')->assertMissing('items/replaced.png');
        Storage::disk('public')->assertExists($result->image);
    }

    public function test_destroy_prevents_deletion_if_has_transactions()
    {
        $product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid
        ]);

        // Mock transaction existence by creating a record in a transaction table
        // For simplicity, we can mock the repository behavior if we want a pure unit test, 
        // but here we use the database to verify the logic.
        \Illuminate\Support\Facades\DB::table('stock_movements')->insert([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'product_uuid' => $product->uuid,
            'location_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'IN',
            'qty' => 10
        ]);

        $entity = new ProductEntity(
            uuid: $product->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid
        );
        
        $result = $this->service->destroy($entity);

        $this->assertEquals('error', $result['status']);
        $this->assertStringContainsString('digunakan dalam transaksi', $result['message']);
    }

    public function test_destroy_calls_repository_delete()
    {
        $product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid
        ]);

        $entity = new ProductEntity(
            uuid: $product->uuid,
            branch_uuid: $this->branch->uuid,
            company_uuid: $this->company->uuid
        );

        $result = $this->service->destroy($entity);

        $this->assertEquals('success', $result['status']);
        $this->assertSoftDeleted('items', ['uuid' => $product->uuid]);
    }
}
