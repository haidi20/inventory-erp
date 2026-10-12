<?php

namespace Database\Seeders\V1;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Operational\Master\Models\PriceLevel;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\ProductAccount;
use App\Modules\Operational\Master\Models\ProductDiscountRule;
use App\Modules\Operational\Master\Models\ProductOpeningBalance;
use App\Modules\Operational\Master\Models\ProductPrice;
use App\Modules\Operational\Master\Models\ProductUnit;
use App\Modules\Operational\Master\Models\Unit;
use App\Modules\Operational\Master\Models\LookupValue;
use Illuminate\Database\Seeder;

class ProductFormEditFixtureSeeder extends Seeder
{
    private const PRODUCT_UUID = '97282e88-883b-4ee2-b7fc-c9a44cfce252';

    private const ACCOUNT_ROLES = [
        'sales' => ['code' => '4.1.9.01', 'name' => 'Fixture Penjualan Produk', 'type' => 'revenue'],
        'inventory' => ['code' => '1.3.9.01', 'name' => 'Fixture Persediaan Produk', 'type' => 'asset'],
        'sales_discount' => ['code' => '4.2.9.01', 'name' => 'Fixture Diskon Penjualan Produk', 'type' => 'revenue'],
        'hpp' => ['code' => '5.1.9.01', 'name' => 'Fixture HPP Produk', 'type' => 'expense'],
        'sales_return' => ['code' => '4.3.9.01', 'name' => 'Fixture Retur Penjualan Produk', 'type' => 'revenue'],
        'purchase_return' => ['code' => '5.2.9.01', 'name' => 'Fixture Retur Pembelian Produk', 'type' => 'expense'],
        'suspend' => ['code' => '2.1.9.01', 'name' => 'Fixture Hutang Suspend Produk', 'type' => 'liability'],
    ];

    public function run(): void
    {
        $branch = Branch::query()->orderBy('id')->first();

        if (!$branch) {
            $this->command?->warn('Product form fixture dilewati: branch belum tersedia.');
            return;
        }

        $unit = Unit::query()->updateOrCreate(
            ['branch_uuid' => $branch->uuid, 'code' => 'FIXTURE-PCS'],
            [
                'company_uuid' => $branch->company_uuid,
                'name' => 'Fixture PCS',
                'is_active' => true,
            ],
        );

        $secondaryUnit = Unit::query()->updateOrCreate(
            ['branch_uuid' => $branch->uuid, 'code' => 'FIXTURE-BOX'],
            [
                'company_uuid' => $branch->company_uuid,
                'name' => 'Fixture Box',
                'is_active' => true,
            ],
        );

        $groupUuid = LookupValue::query()
            ->join('lookup_sets', 'lookup_sets.uuid', '=', 'lookup_values.lookup_set_uuid')
            ->where('lookup_sets.code', 'KELOMPOK')
            ->where('lookup_values.display_name', 'BAK AIR')
            ->where('lookup_values.active_flag', 'Y')
            ->value('lookup_values.uuid');

        $typeUuid = LookupValue::query()
            ->join('lookup_sets', 'lookup_sets.uuid', '=', 'lookup_values.lookup_set_uuid')
            ->where('lookup_sets.code', 'TIPE')
            ->where('lookup_values.display_name', 'SUDUT')
            ->where('lookup_values.active_flag', 'Y')
            ->value('lookup_values.uuid');

        $brandUuid = LookupValue::query()
            ->join('lookup_sets', 'lookup_sets.uuid', '=', 'lookup_values.lookup_set_uuid')
            ->where('lookup_sets.code', 'MERK')
            ->where('lookup_values.display_name', 'LG')
            ->where('lookup_values.active_flag', 'Y')
            ->value('lookup_values.uuid');

        $originUuid = LookupValue::query()
            ->join('lookup_sets', 'lookup_sets.uuid', '=', 'lookup_values.lookup_set_uuid')
            ->where('lookup_sets.code', 'MADE_IN')
            ->where('lookup_values.display_name', 'INDONESIA')
            ->where('lookup_values.active_flag', 'Y')
            ->value('lookup_values.uuid');

        $gradeUuid = LookupValue::query()
            ->join('lookup_sets', 'lookup_sets.uuid', '=', 'lookup_values.lookup_set_uuid')
            ->where('lookup_sets.code', 'GRADE')
            ->where('lookup_values.display_name', 'BHP')
            ->where('lookup_values.active_flag', 'Y')
            ->value('lookup_values.uuid');

        $product = Product::query()->updateOrCreate(
            ['uuid' => self::PRODUCT_UUID],
            [
                'branch_uuid' => $branch->uuid,
                'company_uuid' => $branch->company_uuid,
                'code' => 'BHP-001',
                'name' => 'ALKOHOL SWABSMALL',
                'barcode' => '899000000123',
                'description' => 'Deskripsi fixture produk edit semua tab',
                'item_name_alt' => 'Kemasan Fixture 100 ml',
                'item_type' => 'goods',
                'base_unit_uuid' => $unit->uuid,
                'item_group_lookup_uuid' => $groupUuid,
                'item_type_lookup_uuid' => $typeUuid,
                'brand_lookup_uuid' => $brandUuid,
                'origin_lookup_uuid' => $originUuid,
                'grade_lookup_uuid' => $gradeUuid,
                'kind' => 'BARANG',
                'location' => 'RAK-FIXTURE-01',
                'note' => 'Catatan fixture produk edit semua tab',
                'remarks' => 'Catatan fixture produk edit semua tab',
                'base_price_buy' => 7500,
                'base_price_sell' => 12500,
                'price_buy' => 7500,
                'price_sell' => 12500,
                'opening_cost' => 7000,
                'min_stock' => 5,
                'max_stock' => 100,
                'max_order' => 50,
                'is_bom_enabled' => true,
                'is_active' => true,
                'active_flag' => 'Y',
            ],
        );

        $location = Location::query()->updateOrCreate(
            ['branch_uuid' => $branch->uuid, 'code' => 'FIXTURE-WH'],
            [
                'company_uuid' => $branch->company_uuid,
                'type' => 'warehouse',
                'name' => 'Fixture Warehouse',
                'is_active' => true,
                'can_sell' => true,
                'is_primary' => false,
            ],
        );

        $secondaryLocation = Location::query()->updateOrCreate(
            ['branch_uuid' => $branch->uuid, 'code' => 'FIXTURE-WH-2'],
            [
                'company_uuid' => $branch->company_uuid,
                'type' => 'warehouse',
                'name' => 'Fixture Warehouse 2',
                'is_active' => true,
                'can_sell' => true,
                'is_primary' => false,
            ],
        );

        ProductUnit::query()
            ->where('item_uuid', $product->uuid)
            ->whereNotIn('unit_uuid', [$unit->uuid, $secondaryUnit->uuid])
            ->delete();

        ProductUnit::query()->updateOrCreate(
            ['item_uuid' => $product->uuid, 'unit_uuid' => $unit->uuid],
            [
                'branch_uuid' => $branch->uuid,
                'company_uuid' => $branch->company_uuid,
                'conversion_ratio' => 1,
                'note' => 'Fixture edit form',
                'is_base' => true,
                'is_active' => true,
                'is_purchase_unit' => true,
                'is_sales_unit' => true,
            ],
        );

        ProductUnit::query()->updateOrCreate(
            ['item_uuid' => $product->uuid, 'unit_uuid' => $secondaryUnit->uuid],
            [
                'branch_uuid' => $branch->uuid,
                'company_uuid' => $branch->company_uuid,
                'conversion_ratio' => 12,
                'note' => 'Satuan konversi fixture',
                'is_base' => false,
                'is_active' => true,
                'is_purchase_unit' => true,
                'is_sales_unit' => true,
            ],
        );

        $priceLevel = PriceLevel::query()
            ->where('branch_uuid', $branch->uuid)
            ->where('company_uuid', $branch->company_uuid)
            ->where('is_active', true)
            ->orderBy('name')
            ->first()
            ?? PriceLevel::query()->updateOrCreate(
                ['branch_uuid' => $branch->uuid, 'code' => 'UMUM'],
                [
                    'company_uuid' => $branch->company_uuid,
                    'name' => 'Umum',
                    'is_active' => true,
                ],
            );

        $secondaryPriceLevel = PriceLevel::query()
            ->where('branch_uuid', $branch->uuid)
            ->where('company_uuid', $branch->company_uuid)
            ->where('is_active', true)
            ->where('uuid', '!=', $priceLevel->uuid)
            ->orderBy('name')
            ->first()
            ?? PriceLevel::query()->updateOrCreate(
                ['branch_uuid' => $branch->uuid, 'code' => 'FIXTURE-GROSIR'],
                [
                    'company_uuid' => $branch->company_uuid,
                    'name' => 'Fixture Grosir',
                    'is_active' => true,
                ],
            );

        ProductPrice::query()->updateOrCreate(
            ['item_uuid' => $product->uuid, 'price_level_uuid' => $priceLevel->uuid],
            [
                'branch_uuid' => $branch->uuid,
                'company_uuid' => $branch->company_uuid,
                'price' => 12500,
                'price_buy' => 7500,
                'discount_amount' => 500,
                'discount_percent' => 4,
                'base_qty' => 1,
                'is_active' => true,
            ],
        );

        ProductPrice::query()->updateOrCreate(
            ['item_uuid' => $product->uuid, 'price_level_uuid' => $secondaryPriceLevel->uuid],
            [
                'branch_uuid' => $branch->uuid,
                'company_uuid' => $branch->company_uuid,
                'price' => 11800,
                'price_buy' => 7200,
                'discount_amount' => 750,
                'discount_percent' => 2.5,
                'base_qty' => 2,
                'is_active' => true,
            ],
        );

        ProductOpeningBalance::query()->updateOrCreate(
            ['item_uuid' => $product->uuid, 'location_uuid' => $location->uuid, 'period_month' => '2026-09'],
            [
                'branch_uuid' => $branch->uuid,
                'company_uuid' => $branch->company_uuid,
                'opening_qty' => 25,
            ],
        );

        ProductOpeningBalance::query()->updateOrCreate(
            ['item_uuid' => $product->uuid, 'location_uuid' => $secondaryLocation->uuid, 'period_month' => '2026-10'],
            [
                'branch_uuid' => $branch->uuid,
                'company_uuid' => $branch->company_uuid,
                'opening_qty' => 8.5,
            ],
        );

        foreach (self::ACCOUNT_ROLES as $role => $config) {
            $account = Account::query()
                ->where('branch_uuid', $branch->uuid)
                ->where('code', $config['code'])
                ->where('is_header', false)
                ->where('is_active', true)
                ->first()
                ?? Account::query()->updateOrCreate(
                    ['branch_uuid' => $branch->uuid, 'code' => $config['code']],
                    [
                        'company_uuid' => $branch->company_uuid,
                        'name' => $config['name'],
                        'type' => $config['type'],
                        'is_header' => false,
                        'is_active' => true,
                    ],
                );

            ProductAccount::query()->updateOrCreate(
                ['item_uuid' => $product->uuid, 'account_role' => $role],
                [
                    'branch_uuid' => $branch->uuid,
                    'company_uuid' => $branch->company_uuid,
                    'account_uuid' => $account->uuid,
                ],
            );
        }

        ProductDiscountRule::query()->updateOrCreate(
            ['item_uuid' => $product->uuid, 'min_qty' => 20, 'discount_type' => 'amount'],
            [
                'branch_uuid' => $branch->uuid,
                'company_uuid' => $branch->company_uuid,
                'unit_uuid' => $unit?->uuid,
                'base_qty' => 2,
                'amount' => 2500,
                'is_active' => true,
            ],
        );

        ProductDiscountRule::query()->updateOrCreate(
            ['item_uuid' => $product->uuid, 'min_qty' => 10, 'discount_type' => 'percentage'],
            [
                'branch_uuid' => $branch->uuid,
                'company_uuid' => $branch->company_uuid,
                'unit_uuid' => $unit?->uuid,
                'base_qty' => 1,
                'amount' => 5,
                'is_active' => true,
            ],
        );

        $this->command?->info("Product form fixture siap: {$product->uuid}");
    }
}
