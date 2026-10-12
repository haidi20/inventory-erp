<?php

namespace Database\Seeders\V1;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\ProductLocation;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Operational\Master\Models\LookupSet;
use App\Modules\Operational\Master\Models\LookupValue;
use App\Modules\Operational\Master\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * ProductSeeder
 *
 * Import products from database/data/products-main.json.
 * Data produk klinik: BHP, Obat, Suplemen, dan lain-lain.
 * 
 * Seed ke cabang sandbox (KSB) untuk testing.
 */
class ProductSeeder extends Seeder
{
    /**
     * Path ke file JSON data produk
     */
    private function dataPath(): string
    {
        return database_path('data/products-main.json');
    }

    /**
     * Load data dari file JSON
     */
    private function loadJson(): array
    {
        $path = $this->dataPath();
        
        if (!file_exists($path)) {
            $this->command->error("JSON file not found: {$path}");
            return [];
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error("JSON parse error: " . json_last_error_msg());
            return [];
        }

        return $data ?? [];
    }

    /**
     * Get or create unit by code
     */
    private function getOrCreateUnit(string $code, string $branchUuid, string $companyUuid): ?string
    {
        $code = strtoupper(trim($code));
        
        // Normalisasi kode unit
        $code = match ($code) {
            'PC', 'PCS', 'PIECES' => 'PCS',
            'TABLET', 'TAB' => 'TABLET',
            'CAP', 'KAPSUL' => 'KAPSUL',
            'BOTOL', 'BOT', 'BTL' => 'BOTOL',
            'TUBE' => 'TUBE',
            'VIAL' => 'VIAL',
            'AMPUL', 'AMP' => 'AMPUL',
            'STRIP' => 'STRIP',
            default => $code,
        };

        if (empty($code)) {
            return null;
        }

        // Cek cache
        static $cache = [];
        if (isset($cache[$code])) {
            return $cache[$code];
        }

        // Cari unit yang sudah ada
        $unit = Unit::where('code', $code)->first();

        if (!$unit) {
            // Buat unit baru
            $unit = Unit::create([
                'uuid' => (string) Str::uuid(),
                'code' => $code,
                'name' => $code,
                'branch_uuid' => $branchUuid,
                'company_uuid' => $companyUuid,
                'is_active' => true,
            ]);
        }

        $cache[$code] = $unit->uuid;
        return $unit->uuid;
    }

    /**
     * Map category ke item_type
     */
    private function mapItemType(?string $category): string
    {
        if (empty($category)) {
            return 'goods';
        }

        $category = strtolower(trim($category));

        return match ($category) {
            'obat keras', 'obat bebas terbatas', 'obat prekursor', 'obat' => 'goods',
            'bhp' => 'goods',
            'suplemen' => 'goods',
            'jasa', 'service' => 'service',
            default => 'goods',
        };
    }

    /**
     * Get or create a lookup value used by the product form.
     */
    private function ensureLookupValue(string $setCode, string $displayName, string $companyUuid): ?string
    {
        $displayName = trim($displayName);
        if ($displayName === '') {
            return null;
        }

        $set = LookupSet::withTrashed()
            ->where('company_uuid', $companyUuid)
            ->where('program_code', $setCode)
            ->first();

        if (! $set) {
            return null;
        }

        $value = LookupValue::withTrashed()
            ->where('lookup_set_uuid', $set->uuid)
            ->where('display_name', $displayName)
            ->where('company_uuid', $companyUuid)
            ->first();

        if ($value) {
            return $value->uuid;
        }

        $nextSeq = (int) (LookupValue::where('lookup_set_uuid', $set->uuid)
            ->where('company_uuid', $companyUuid)
            ->max('seq') ?? 0) + 1;

        $value = LookupValue::create([
            'uuid' => (string) Str::uuid(),
            'lookup_set_uuid' => $set->uuid,
            'code' => $displayName,
            'display_name' => $displayName,
            'value' => $displayName,
            'program_code' => $displayName,
            'seq' => $nextSeq,
            'order_priority' => $nextSeq,
            'is_active' => true,
            'active_flag' => 'Y',
            'primary_flag' => 'N',
            'company_uuid' => $companyUuid,
            'created_at' => now()->timestamp,
            'updated_at' => now()->timestamp,
        ]);

        return $value->uuid;
    }

    public function run(): void
    {
        $this->command->info('=== ProductSeeder ===');

        // Load data dari JSON
        $products = $this->loadJson();

        if (empty($products)) {
            $this->command->error('No products found in JSON file.');
            return;
        }

        $this->command->info('Loaded ' . count($products) . ' products from JSON');

        // Get sandbox branch (KSB) - branch untuk testing
        $branch = Branch::where('short_name', RuntimeCompanySeeder::SANDBOX_BRANCH_SHORT_NAME)->first();

        if (!$branch) {
            $this->command->error('Sandbox branch (KSB) not found. Please run RuntimeCompanySeeder first.');
            return;
        }

        $companyUuid = $branch->company_uuid;

        $primaryWarehouse = Location::where('branch_uuid', $branch->uuid)
            ->where('company_uuid', $companyUuid)
            ->where('type', 'warehouse')
            ->orderByDesc('is_primary')
            ->orderBy('uuid')
            ->first();

        // Statistics
        $stats = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        // Process each product
        foreach ($products as $product) {
            $code = $product['code'] ?? null;
            $name = $product['name'] ?? null;

            if (empty($code) || empty($name)) {
                $stats['skipped']++;
                continue;
            }

            // Get or create unit
            $unitUuid = null;
            if (!empty($product['unit'])) {
                $unitUuid = $this->getOrCreateUnit($product['unit'], $branch->uuid, $companyUuid);
            }

            // Determine item type
            $itemType = $this->mapItemType($product['category'] ?? null);

            // Prepare data
            $data = [
                'company_uuid' => $companyUuid,
                'name' => $name,
                'unit' => $product['unit'] ?? null,
                'category' => $product['category'] ?? null,
                'brand' => $product['manufacturer'] ?? null,
                'item_type' => $itemType,
                'item_category_lookup_uuid' => $this->ensureLookupValue('TIPE', $product['category'] ?? '', $companyUuid),
                'item_group_lookup_uuid' => $this->ensureLookupValue('KELOMPOK', $product['group_name'] ?? '', $companyUuid),
                'item_type_lookup_uuid' => $this->ensureLookupValue('TIPE', $product['category'] ?? '', $companyUuid),
                'brand_lookup_uuid' => $this->ensureLookupValue('MERK', $product['manufacturer'] ?? '', $companyUuid),
                'kind' => $this->ensureLookupValue('JENIS', 'BARANG', $companyUuid),
                'base_unit_uuid' => $unitUuid,
                'price_buy' => (float) ($product['price_buy'] ?? 0),
                'price_sell' => (float) ($product['price_sell'] ?? 0),
                'latest_buy_price' => (float) ($product['price_buy'] ?? 0),
                'latest_sell_price' => (float) ($product['price_sell'] ?? 0),
                'base_price_buy' => (float) ($product['price_buy'] ?? 0),
                'base_price_sell' => (float) ($product['price_sell'] ?? 0),
                'opening_cost' => (float) ($product['price_buy'] ?? 0),
                'standard_cost' => (float) ($product['price_buy'] ?? 0),
                'min_stock' => (int) ($product['min_stock'] ?? 0),
                'is_active' => (bool) ($product['is_active'] ?? true),
                'active_flag' => ($product['is_active'] ?? true) ? 'Y' : 'N',
            ];

            // Check if product exists
            $existing = Product::where('branch_uuid', $branch->uuid)
                ->where('code', $code)
                ->where('deleted_at', 0)
                ->first();

            if ($existing) {
                $existing->update($data);
                $stats['updated']++;
                $productModel = $existing;
            } else {
                $productModel = Product::create(array_merge($data, [
                    'uuid' => (string) Str::uuid(),
                    'branch_uuid' => $branch->uuid,
                    'code' => $code,
                ]));
                $stats['created']++;
            }

            $stock = (float) ($product['stock'] ?? 0);
            if ($stock > 0 && $primaryWarehouse) {
                ProductLocation::updateOrCreate(
                    [
                        'item_uuid' => $productModel->uuid,
                        'source_type' => 'location',
                        'source_id' => $primaryWarehouse->uuid,
                        'deleted_at' => 0,
                    ],
                    [
                        'uuid' => (string) Str::uuid(),
                        'branch_uuid' => $branch->uuid,
                        'company_uuid' => $companyUuid,
                        'location_uuid' => $primaryWarehouse->uuid,
                        'on_hand_qty' => $stock,
                        'reserved_qty' => 0,
                        'available_qty' => $stock,
                        'avg_cost' => (float) ($product['price_buy'] ?? 0),
                    ],
                );
            }
        }

        // Output results
        $this->command->info("  ✓ Created: {$stats['created']}");
        $this->command->info("  ✓ Updated: {$stats['updated']}");
        $this->command->info("  ✓ Skipped: {$stats['skipped']}");
        $this->command->info('=== ProductSeeder DONE ===');
    }
}
