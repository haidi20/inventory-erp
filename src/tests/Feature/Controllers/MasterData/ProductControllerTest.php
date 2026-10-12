<?php

namespace Tests\Feature\Controllers\MasterData;

use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    private User $user;

    private Branch $branch;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->user = User::factory()->create();
        UserCompany::create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->company->uuid,
            'is_default' => true,
        ]);
        UserBranch::create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->company->uuid,
            'branch_uuid' => $this->branch->uuid,
            'is_default' => true,
        ]);

        DB::table('item_images')->delete();
        DB::table('items')->delete();

        // Bypass Auth & Permissions to focus on Controller/Repository logic
        $this->withoutMiddleware();
    }

    /**
     * Test Index (Pagination)
     */
    public function test_can_list_products()
    {
        Product::factory()->count(15)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/products?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'total',
                    'page',
                    'per_page',
                ],
            ])
            ->assertJsonPath('meta.total', 15)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_index_only_returns_products_from_authenticated_users_company()
    {
        Product::factory()->create([
            'code' => 'API-SBX',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        Product::factory()->create([
            'code' => 'API-OTHER',
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'API-SBX');
    }

    public function test_superadmin_can_filter_products_by_company_and_defaults_to_demo_company()
    {
        $role = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $superadmin = User::factory()->create();
        UserCompany::create([
            'user_uuid' => $superadmin->uuid,
            'company_uuid' => $this->company->uuid,
            'is_default' => true,
        ]);
        UserBranch::create([
            'user_uuid' => $superadmin->uuid,
            'company_uuid' => $this->company->uuid,
            'branch_uuid' => $this->branch->uuid,
            'is_default' => true,
        ]);
        $superadmin->assignRole($role);

        Product::factory()->create([
            'code' => 'API-SUPER-A',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $otherCompany = Company::factory()->create(['name' => 'Company B']);
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        Product::factory()->create([
            'code' => 'API-SUPER-B',
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $this->actingAs($superadmin)->getJson('/api/v1/products')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'API-SUPER-A');

        $this->actingAs($superadmin)->getJson('/api/v1/products?filter_company_uuid=all')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $this->actingAs($superadmin)->getJson("/api/v1/products?filter_company_uuid={$otherCompany->uuid}")
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'API-SUPER-B');
    }

    /**
     * Test Store (Create)
     */
    public function test_can_create_product()
    {
        $payload = [
            'code' => 'PRD-001',
            'name' => 'Tablet Amoxicillin',
            'category' => 'Obat',
            'price_buy' => 5000,
            'price_sell' => 7500,
            'stock' => 100,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/products', $payload);

        // Note: Controller returns 200 via apiResource by default if not specified
        $response->assertStatus(200);
        $this->assertDatabaseHas('items', [
            'code' => 'PRD-001',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
    }

    /**
     * Test Store (Create) with product files
     */
    public function test_can_store_product_files()
    {
        Storage::fake('public');

        $payload = [
            'code' => 'PRD-FILE',
            'name' => 'Tablet Amoxicillin File',
            'category' => 'Obat',
            'price_buy' => 5000,
            'price_sell' => 7500,
            'images' => [
                UploadedFile::fake()->image('product.png'),
                UploadedFile::fake()->createWithContent('document.pdf', '%PDF-1.4'),
            ],
        ];

        $response = $this->actingAs($this->user)
            ->post('/api/v1/products', $payload);

        $response->assertStatus(200);
        $product = Product::query()->where('code', 'PRD-FILE')->firstOrFail();
        $this->assertDatabaseCount('item_images', 2);
        $this->assertDatabaseHas('item_images', [
            'item_uuid' => $product->uuid,
            'path' => $product->image,
            'sort_order' => 0,
        ]);
        Storage::disk('public')->assertExists($product->image);
    }

    /**
     * Test Show
     */
    public function test_can_show_product()
    {
        $product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/products/{$product->uuid}");

        $response->assertStatus(200)
            ->assertJsonPath('data.uuid', $product->uuid);
    }

    /**
     * Test Update
     */
    public function test_can_update_product()
    {
        $product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $payload = [
            'name' => 'Amoxicillin 500mg Updated',
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/products/{$product->uuid}", $payload);

        $response->assertStatus(200);
        $this->assertEquals('Amoxicillin 500mg Updated', $product->fresh()->name);
    }

    /**
     * Test Delete (Soft Delete)
     */
    public function test_can_delete_product()
    {
        Product::query()->delete(); // Clear
        $product = Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/products/{$product->uuid}");

        $response->assertStatus(204);
        $this->assertNotEquals(0, $product->fresh()->deleted_at);
    }
}
