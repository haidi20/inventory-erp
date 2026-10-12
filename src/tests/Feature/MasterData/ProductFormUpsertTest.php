<?php

namespace Tests\Feature\MasterData;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Operational\Master\Models\LookupSet;
use App\Modules\Operational\Master\Models\LookupValue;
use App\Modules\Operational\Master\Models\PriceLevel;
use App\Modules\Operational\Master\Models\Unit;
use App\Modules\Operational\Master\Services\ProductService;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductFormUpsertTest extends TestCase
{
    private User $user;
    private Company $company;
    private Branch $branch;
    private string $unitUuid;
    private string $priceLevelUuid;
    private string $locationUuid;
    private ?string $productUuid = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create([
            'company_uuid' => $this->company->uuid,
        ]);
        $this->user = User::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $this->withoutMiddleware();
        $this->unitUuid = (string) Str::uuid();
        Unit::create([
            'uuid' => $this->unitUuid,
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'code' => 'FORM-UPSERT-UNIT',
            'name' => 'Form Upsert Unit',
            'is_active' => true,
        ]);
        $this->priceLevelUuid = (string) Str::uuid();
        $this->locationUuid = (string) Str::uuid();
    }

    protected function tearDown(): void
    {
        if ($this->productUuid) {
            $product = Product::find($this->productUuid);

            if ($product) {
                $product->units()->delete();
                $product->prices()->delete();
                $product->openingBalances()->delete();
                $product->discountRules()->delete();
                $product->accounts()->delete();
                $product->delete();
            }
        }

        parent::tearDown();
    }

    public function test_create_then_update_persists_all_form_tabs_upsert(): void
    {
        $unitData = [
            'unit_uuid' => $this->unitUuid,
            'conversion_ratio' => 7.5,
            'note' => 'Tab unit note',
            'is_default' => true,
            'is_active' => true,
        ];
        $priceData = [
            'price_level_uuid' => $this->priceLevelUuid,
            'price_buy' => 8250.5,
            'price_sell' => 14250.75,
            'discount_amount' => 950.25,
            'discount_percent' => 8.5,
            'base_qty' => 2.5,
            'is_active' => true,
        ];
        $discountData = [
            'quantity' => 3,
            'unit_uuid' => $this->unitUuid,
            'discount_percent' => 12.5,
            'discount_amount' => 0,
            'base_qty' => 4.5,
        ];

        $create = $this->actingAs($this->user)->postJson('/api/v1/products', $this->payload([
            'code' => 'FORM-UPSERT-CREATE',
            'units' => [$unitData],
            'prices' => [$priceData],
            'discounts' => [$discountData],
        ]));
        $create->assertStatus(200);
        $this->productUuid = $create->json('data.uuid');

        $this->assertDatabaseHas('item_units', [
            'item_uuid' => $this->productUuid,
            'unit_uuid' => $this->unitUuid,
            'conversion_ratio' => 7.5,
            'note' => 'Tab unit note',
            'is_base' => true,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('item_prices', [
            'item_uuid' => $this->productUuid,
            'price_level_uuid' => $this->priceLevelUuid,
            'price' => 14250.75,
            'price_buy' => 8250.5,
            'discount_amount' => 950.25,
            'discount_percent' => 8.5,
            'base_qty' => 2.5,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('item_discount_rules', [
            'item_uuid' => $this->productUuid,
            'unit_uuid' => $this->unitUuid,
            'discount_type' => 'percentage',
            'min_qty' => 3,
            'base_qty' => 4.5,
            'amount' => 12.5,
        ]);

        $unitData['conversion_ratio'] = 9.25;
        $unitData['note'] = 'Updated unit note';
        $unitData['is_active'] = false;
        $priceData['price_buy'] = 9375.25;
        $priceData['price_sell'] = 16875.5;
        $priceData['discount_amount'] = 1250.75;
        $priceData['discount_percent'] = 11.25;
        $priceData['base_qty'] = 3.75;
        $discountData['unit_uuid'] = null;
        $discountData['discount_percent'] = 0;
        $discountData['discount_amount'] = 2500;
        $discountData['base_qty'] = 5.25;

        $update = $this->actingAs($this->user)->putJson(
            "/api/v1/products/{$this->productUuid}",
            $this->payload([
                'code' => 'FORM-UPSERT-UPDATE',
                'units' => [$unitData],
                'prices' => [$priceData],
                'discounts' => [$discountData],
            ])
        );
        $update->assertStatus(200);

        $this->assertDatabaseHas('items', [
            'uuid' => $this->productUuid,
            'code' => 'FORM-UPSERT-UPDATE',
        ]);
        $this->assertDatabaseHas('item_units', [
            'item_uuid' => $this->productUuid,
            'unit_uuid' => $this->unitUuid,
            'conversion_ratio' => 9.25,
            'note' => 'Updated unit note',
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('item_prices', [
            'item_uuid' => $this->productUuid,
            'price_level_uuid' => $this->priceLevelUuid,
            'price' => 16875.5,
            'price_buy' => 9375.25,
            'discount_amount' => 1250.75,
            'discount_percent' => 11.25,
            'base_qty' => 3.75,
        ]);
        $this->assertDatabaseHas('item_discount_rules', [
            'item_uuid' => $this->productUuid,
            'unit_uuid' => null,
            'discount_type' => 'amount',
            'min_qty' => 3,
            'base_qty' => 5.25,
            'amount' => 2500,
        ]);

        $loaded = app(ProductService::class)->getProductForEdit(
            $this->productUuid,
            $this->branch->uuid,
            $this->company->uuid,
        );

        $this->assertNotNull($loaded);
        $this->assertSame('Updated unit note', $loaded->units[0]['note']);
        $this->assertFalse($loaded->units[0]['is_active']);
        $this->assertSame(9375.25, $loaded->prices[0]['price_buy']);
        $this->assertSame(1250.75, $loaded->prices[0]['discount_amount']);
        $this->assertSame(11.25, $loaded->prices[0]['discount_percent']);
        $this->assertSame(3.75, $loaded->prices[0]['base_qty']);
        $this->assertSame(5.25, $loaded->discounts[0]['base_qty']);
        $this->assertNull($loaded->discounts[0]['unit_uuid']);
    }

    public function test_update_persists_every_product_form_input_and_tab_to_database(): void
    {
        $unitA = Unit::create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'code' => 'ALL-FORM-PCS',
            'name' => 'Original PCS',
            'is_active' => true,
        ]);
        $unitB = Unit::create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'code' => 'ALL-FORM-BOX',
            'name' => 'Updated Box',
            'is_active' => true,
        ]);

        $lookupSet = LookupSet::create([
            'uuid' => (string) Str::uuid(),
            'code' => 'ALL-FORM-LOOKUP',
            'name' => 'All Form Lookup',
            'source' => 'app',
            'program_code' => 'ALL_FORM_LOOKUP',
            'company_uuid' => $this->company->uuid,
            'user_can_edit_flag' => 'Y',
            'is_active' => true,
        ]);
        $makeLookup = fn (string $name): LookupValue => LookupValue::create([
            'uuid' => (string) Str::uuid(),
            'lookup_set_uuid' => $lookupSet->uuid,
            'code' => strtoupper(str_replace(' ', '_', $name)),
            'display_name' => $name,
            'value' => $name,
            'program_code' => strtoupper(str_replace(' ', '_', $name)),
            'seq' => 1,
            'order_priority' => 1,
            'company_uuid' => $this->company->uuid,
            'is_active' => true,
            'active_flag' => 'Y',
        ]);
        $groupUuid = $makeLookup('Original Group')->uuid;
        $typeUuid = $makeLookup('Original Type')->uuid;
        $brandUuid = $makeLookup('Original Brand')->uuid;
        $originUuid = $makeLookup('Original Origin')->uuid;
        $gradeUuid = $makeLookup('Original Grade')->uuid;

        $priceLevelA = PriceLevel::create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'code' => 'ALL-FORM-RETAIL',
            'name' => 'Updated Retail',
            'is_active' => true,
        ]);
        $priceLevelB = PriceLevel::create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'code' => 'ALL-FORM-GROSIR',
            'name' => 'Updated Grosir',
            'is_active' => true,
        ]);

        $warehouseA = Location::create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'type' => 'warehouse',
            'code' => 'ALL-FORM-WH-A',
            'name' => 'Updated Warehouse A',
            'is_active' => true,
        ]);
        $warehouseB = Location::create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'type' => 'warehouse',
            'code' => 'ALL-FORM-WH-B',
            'name' => 'Updated Warehouse B',
            'is_active' => true,
        ]);

        $accountRoles = [
            'sales' => ['code' => 'ALL-FORM-SALES', 'type' => 'revenue'],
            'inventory' => ['code' => 'ALL-FORM-INVENTORY', 'type' => 'asset'],
            'sales_discount' => ['code' => 'ALL-FORM-DISCOUNT', 'type' => 'revenue'],
            'hpp' => ['code' => 'ALL-FORM-HPP', 'type' => 'expense'],
            'sales_return' => ['code' => 'ALL-FORM-SALES-RETURN', 'type' => 'revenue'],
            'purchase_return' => ['code' => 'ALL-FORM-PURCHASE-RETURN', 'type' => 'expense'],
            'suspend' => ['code' => 'ALL-FORM-SUSPEND', 'type' => 'liability'],
        ];
        $accounts = [];
        foreach ($accountRoles as $role => $config) {
            $accounts[$role] = Account::create([
                'uuid' => (string) Str::uuid(),
                'branch_uuid' => $this->branch->uuid,
                'company_uuid' => $this->company->uuid,
                'code' => $config['code'],
                'name' => ucfirst(str_replace('_', ' ', $role)).' Account',
                'type' => $config['type'],
                'is_header' => false,
                'is_active' => true,
            ]);
        }

        $create = $this->actingAs($this->user)->postJson('/api/v1/products', $this->payload([
            'code' => 'ALL-FORM-CREATE',
            'name' => 'Original Name',
            'base_unit_uuid' => $unitA->uuid,
            'group_uuid' => $groupUuid,
            'type_uuid' => $typeUuid,
            'brand_uuid' => $brandUuid,
            'origin_uuid' => $originUuid,
            'grade_uuid' => $gradeUuid,
            'location' => 'OLD-LOCATION',
            'accounts' => [
                ['account_role' => 'inventory', 'account_uuid' => $accounts['inventory']->uuid],
            ],
            'units' => [
                ['unit_uuid' => $unitA->uuid, 'conversion_ratio' => 1, 'note' => 'Original unit', 'is_default' => true, 'is_active' => true],
            ],
            'opening_balances' => [
                ['location_uuid' => $warehouseA->uuid, 'period' => '2026-09', 'opening_qty' => 1],
            ],
            'prices' => [
                ['price_level_uuid' => $priceLevelA->uuid, 'price_buy' => 1000, 'price_sell' => 2000, 'discount_amount' => 0, 'discount_percent' => 0, 'base_qty' => 1, 'is_active' => true],
            ],
            'discounts' => [
                ['quantity' => 10, 'unit_uuid' => $unitA->uuid, 'discount_percent' => 1, 'discount_amount' => 0, 'base_qty' => 1],
            ],
        ]));
        self::assertSame(200, $create->status(), $create->getContent());
        $this->productUuid = $create->json('data.uuid');

        $updatedGroupUuid = $makeLookup('Updated Group')->uuid;
        $updatedTypeUuid = $makeLookup('Updated Type')->uuid;
        $updatedBrandUuid = $makeLookup('Updated Brand')->uuid;
        $updatedOriginUuid = $makeLookup('Updated Origin')->uuid;
        $updatedGradeUuid = $makeLookup('Updated Grade')->uuid;

        $update = $this->actingAs($this->user)->putJson(
            '/api/v1/products/'.$this->productUuid,
            $this->payload([
            'code' => 'ALL-FORM-UPDATE',
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'item_name_alt' => 'Updated kemasan',
            'barcode' => '899000000777',
            'base_unit_uuid' => $unitB->uuid,
            'group_uuid' => $updatedGroupUuid,
            'type_uuid' => $updatedTypeUuid,
            'brand_uuid' => $updatedBrandUuid,
            'origin_uuid' => $updatedOriginUuid,
            'grade_uuid' => $updatedGradeUuid,
            'kind' => 'Updated Kind',
            'location' => 'UPDATED-LOCATION',
            'remarks' => 'Updated note',
            'price_buy' => 8123.45,
            'price_sell' => 15234.56,
            'opening_cost' => 7987.65,
            'min_stock' => 6,
            'max_stock' => 120,
            'max_order' => 65,
            'is_assembly' => true,
            'is_active' => false,
            'accounts' => collect($accountRoles)
                ->map(fn (array $config, string $role) => [
                    'account_role' => $role,
                    'account_uuid' => $accounts[$role]->uuid,
                ])->values()->all(),
            'units' => [
                ['unit_uuid' => $unitB->uuid, 'conversion_ratio' => 12.5, 'note' => 'Updated unit', 'is_default' => true, 'is_active' => true],
                ['unit_uuid' => $unitA->uuid, 'conversion_ratio' => 3.5, 'note' => 'Updated secondary unit', 'is_default' => false, 'is_active' => false],
            ],
            'opening_balances' => [
                ['location_uuid' => $warehouseA->uuid, 'period' => '2026-10', 'opening_qty' => 15.25],
                ['location_uuid' => $warehouseB->uuid, 'period' => '2026-10', 'opening_qty' => 8.75],
            ],
            'prices' => [
                ['price_level_uuid' => $priceLevelA->uuid, 'price_buy' => 7123.25, 'price_sell' => 14123.75, 'discount_amount' => 325.5, 'discount_percent' => 0, 'base_qty' => 2.25, 'is_active' => true],
                ['price_level_uuid' => $priceLevelB->uuid, 'price_buy' => 6500, 'price_sell' => 12500, 'discount_amount' => 0, 'discount_percent' => 7.5, 'base_qty' => 3.5, 'is_active' => false],
            ],
            'discounts' => [
                ['quantity' => 12, 'unit_uuid' => $unitB->uuid, 'discount_percent' => 9.5, 'discount_amount' => 0, 'base_qty' => 2.5],
                ['quantity' => 24, 'unit_uuid' => $unitB->uuid, 'discount_percent' => 0, 'discount_amount' => 3250.25, 'base_qty' => 4.5],
            ],
        ]));
        self::assertSame(200, $update->status(), $update->getContent());

        $this->assertDatabaseHas('items', [
            'uuid' => $this->productUuid,
            'code' => 'ALL-FORM-UPDATE',
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'item_name_alt' => 'Updated kemasan',
            'barcode' => '899000000777',
            'base_unit_uuid' => $unitB->uuid,
            'kind' => 'Updated Kind',
            'location' => 'UPDATED-LOCATION',
            'storage_bin_code' => 'UPDATED-LOCATION',
            'note' => 'Updated note',
            'remarks' => 'Updated note',
            'base_price_buy' => 8123.45,
            'base_price_sell' => 15234.56,
            'opening_cost' => 7987.65,
            'min_stock' => 6,
            'max_stock' => 120,
            'max_order' => 65,
            'is_bom_enabled' => true,
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('items', [
            'uuid' => $this->productUuid,
            'item_group_lookup_uuid' => $updatedGroupUuid,
            'item_type_lookup_uuid' => $updatedTypeUuid,
            'brand_lookup_uuid' => $updatedBrandUuid,
            'origin_lookup_uuid' => $updatedOriginUuid,
            'grade_lookup_uuid' => $updatedGradeUuid,
        ]);
        $this->assertDatabaseHas('item_units', [
            'item_uuid' => $this->productUuid,
            'unit_uuid' => $unitB->uuid,
            'conversion_ratio' => 12.5,
            'note' => 'Updated unit',
            'is_base' => true,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('item_units', [
            'item_uuid' => $this->productUuid,
            'unit_uuid' => $unitA->uuid,
            'conversion_ratio' => 3.5,
            'note' => 'Updated secondary unit',
            'is_base' => false,
            'is_active' => false,
        ]);
        foreach ($accounts as $role => $account) {
            $this->assertDatabaseHas('item_accounts', [
                'item_uuid' => $this->productUuid,
                'account_role' => $role,
                'account_uuid' => $account->uuid,
            ]);
        }
        $this->assertDatabaseHas('item_opening_balances', [
            'item_uuid' => $this->productUuid,
            'location_uuid' => $warehouseA->uuid,
            'period_month' => '2026-10',
            'opening_qty' => 15.25,
        ]);
        $this->assertDatabaseHas('item_opening_balances', [
            'item_uuid' => $this->productUuid,
            'location_uuid' => $warehouseB->uuid,
            'period_month' => '2026-10',
            'opening_qty' => 8.75,
        ]);
        $this->assertDatabaseHas('item_prices', [
            'item_uuid' => $this->productUuid,
            'price_level_uuid' => $priceLevelA->uuid,
            'price' => 14123.75,
            'price_buy' => 7123.25,
            'discount_amount' => 325.5,
            'discount_percent' => 0,
            'base_qty' => 2.25,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('item_prices', [
            'item_uuid' => $this->productUuid,
            'price_level_uuid' => $priceLevelB->uuid,
            'price' => 12500,
            'price_buy' => 6500,
            'discount_amount' => 0,
            'discount_percent' => 7.5,
            'base_qty' => 3.5,
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('item_discount_rules', [
            'item_uuid' => $this->productUuid,
            'unit_uuid' => $unitB->uuid,
            'discount_type' => 'percentage',
            'min_qty' => 12,
            'base_qty' => 2.5,
            'amount' => 9.5,
        ]);
        $this->assertDatabaseHas('item_discount_rules', [
            'item_uuid' => $this->productUuid,
            'unit_uuid' => $unitB->uuid,
            'discount_type' => 'amount',
            'min_qty' => 24,
            'base_qty' => 4.5,
            'amount' => 3250.25,
        ]);

        $loaded = app(ProductService::class)->getProductForEdit(
            $this->productUuid,
            $this->branch->uuid,
            $this->company->uuid,
        );

        $this->assertNotNull($loaded);
        $this->assertSame('ALL-FORM-UPDATE', $loaded->code);
        $this->assertSame('Updated Name', $loaded->name);
        $this->assertSame('Updated description', $loaded->description);
        $this->assertSame('Updated kemasan', $loaded->item_name_alt);
        $this->assertSame('899000000777', $loaded->barcode);
        $this->assertSame($unitB->uuid, $loaded->toArray()['base_unit_uuid']);
        $this->assertSame('Updated Kind', $loaded->kind);
        $this->assertSame('UPDATED-LOCATION', $loaded->location);
        $this->assertSame('Updated note', $loaded->remarks);
        $this->assertSame(8123.45, $loaded->price_buy);
        $this->assertSame(15234.56, $loaded->price_sell);
        $this->assertSame(7987.65, $loaded->opening_cost);
        $this->assertSame(6.0, $loaded->min_stock);
        $this->assertSame(120.0, $loaded->max_stock);
        $this->assertSame(65.0, $loaded->max_order);
        $this->assertTrue($loaded->is_assembly);
        $this->assertFalse($loaded->is_active);
        $this->assertCount(2, $loaded->units);
        $this->assertCount(7, $loaded->accounts);
        $this->assertCount(2, $loaded->opening_balances);
        $this->assertCount(2, $loaded->prices);
        $this->assertCount(2, $loaded->discounts);
    }

    public function test_create_rejects_duplicate_product_code(): void
    {
        Product::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'code' => 'DUPLICATE-CODE',
            'name' => 'Produk Existing',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/products', $this->payload([
            'code' => 'duplicate-code',
        ]));

        $response
            ->assertStatus(422)
            ->assertJsonPath('errors.code.0', 'Kode produk sudah digunakan.');
    }

    public function test_nested_rows_reject_duplicates_and_mixed_discount_values(): void
    {
        $payload = $this->payload([
            'code' => 'ROW-VALIDATION-001',
            'units' => [
                ['unit_uuid' => $this->unitUuid, 'conversion_ratio' => 1, 'is_default' => true, 'is_active' => true],
                ['unit_uuid' => $this->unitUuid, 'conversion_ratio' => 2, 'is_default' => false, 'is_active' => true],
            ],
        ]);
        $response = $this->actingAs($this->user)->postJson('/api/v1/products', $payload);
        $this->assertSame(422, $response->status());
        $this->assertStringContainsString('Satuan duplikat.', json_encode($response->json()));

        $payload = $this->payload([
            'code' => 'ROW-VALIDATION-002',
            'discounts' => [['quantity' => 2, 'unit_uuid' => $this->unitUuid, 'discount_percent' => 5, 'discount_amount' => 100, 'base_qty' => 1]],
        ]);
        $response = $this->actingAs($this->user)->postJson('/api/v1/products', $payload);
        $this->assertSame(422, $response->status());
        $this->assertStringContainsString('Diskon persen dan nominal tidak boleh diisi bersamaan.', json_encode($response->json()));
    }
    public function test_nested_rows_reject_remaining_duplicate_types(): void
    {
        $accountUuid = (string) Str::uuid();
        $payload = $this->payload([
            'code' => 'ROW-VALIDATION-ACCOUNT',
            'accounts' => [
                ['account_role' => 'sales', 'account_uuid' => $accountUuid],
                ['account_role' => 'sales', 'account_uuid' => $accountUuid],
            ],
        ]);
        $response = $this->actingAs($this->user)->postJson('/api/v1/products', $payload);
        $this->assertSame(422, $response->status());
        $errors = $response->json('errors');
        $this->assertSame('Role akun duplikat.', $errors['accounts.1'][0] ?? null);

        $payload = $this->payload([
            'code' => 'ROW-VALIDATION-OPENING',
            'opening_balances' => [
                ['location_uuid' => $this->locationUuid, 'opening_qty' => 1, 'period' => '2026-09'],
                ['location_uuid' => $this->locationUuid, 'opening_qty' => 2, 'period' => '2026-09'],
            ],
        ]);
        $response = $this->actingAs($this->user)->postJson('/api/v1/products', $payload);
        $this->assertSame(422, $response->status());
        $errors = $response->json('errors');
        $this->assertSame('Gudang/periode saldo duplikat.', $errors['opening_balances.1'][0] ?? null);

        $payload = $this->payload([
            'code' => 'ROW-VALIDATION-PRICE',
            'prices' => [
                ['price_level_uuid' => $this->priceLevelUuid, 'price_buy' => 1000, 'price_sell' => 1500],
                ['price_level_uuid' => $this->priceLevelUuid, 'price_buy' => 1100, 'price_sell' => 1600],
            ],
        ]);
        $response = $this->actingAs($this->user)->postJson('/api/v1/products', $payload);
        $this->assertSame(422, $response->status());
        $errors = $response->json('errors');
        $this->assertSame('Level harga duplikat.', $errors['prices.1'][0] ?? null);
    }
    private function payload(array $overrides): array
    {
        return array_merge([
            'name' => 'Produk Real Data All Tabs',
            'item_name_alt' => 'Paket 10 Tablet',
            'description' => 'Deskripsi produk testing',
            'barcode' => '899000000001',
            'base_unit_uuid' => $this->unitUuid,
            'kind' => 'Obat',
            'location' => 'A-01-02',
            'price_buy' => 7500,
            'price_sell' => 12500,
            'opening_cost' => 7250.25,
            'min_stock' => 5,
            'max_stock' => 100,
            'max_order' => 75,
            'is_active' => true,
            'is_assembly' => false,
            'accounts' => [],
            'opening_balances' => [],
        ], $overrides);
    }
}
