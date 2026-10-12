<?php

namespace Database\Seeders\V1;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\ProductLocation;
use App\Modules\Operational\Master\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class ProductDataSeeder extends Seeder
{
    private function dataPath(string $file): string
    {
        return database_path('data/'.$file);
    }

    private function loadJson(string $file): array
    {
        $path = $this->dataPath($file);
        if (! file_exists($path)) {
            $this->command->error("JSON file not found: {$path}");

            return [];
        }

        return json_decode(file_get_contents($path), true) ?? [];
    }

    public function run(): void
    {
        $this->command->info('=== Product Data Seeder ===');

        $branch = Branch::orderBy('id')->first();
        if (! $branch) {
            throw new RuntimeException('Product data seeder requires at least one branch.');
        }

        $this->seedProducts($branch->uuid, $branch->company_uuid);

        $this->command->info('=== Product Data Seeder DONE ===');
    }

    private function seedProducts(string $branchUuid, string $companyUuid): void
    {
        $existing = Product::where('code', 'like', '%-BHP-%')
            ->orWhere('code', 'like', '%-OBT-%')
            ->count();
        if ($existing > 0) {
            $this->command->info("  Products already seeded ({$existing}). Skipping.");

            return;
        }

        $products = $this->loadJson('products.json');
        if (! $products) {
            return;
        }

        $unitMap = Unit::pluck('uuid', 'name')->toArray();

        Product::transaction(function () use ($products, $branchUuid, $companyUuid, $unitMap) {
            foreach ($products as $productData) {
                $priceBuy = (float) ($productData['price_buy'] ?? 0);
                $priceSell = (float) ($productData['price_sell'] ?: ($priceBuy * 1.25));

                $product = Product::updateOrCreate(
                    [
                        'branch_uuid' => $branchUuid,
                        'code' => $productData['code'],
                    ],
                    [
                        'company_uuid' => $companyUuid,
                        'name' => $productData['name'],
                        'item_type' => 'goods',
                        'base_unit_uuid' => $unitMap[$productData['unit']] ?? null,
                        'unit' => $productData['unit'],
                        'category' => $productData['category'],
                        'price_buy' => $priceBuy,
                        'price_sell' => $priceSell,
                        'latest_buy_price' => $priceBuy,
                        'latest_sell_price' => $priceSell,
                        'base_price_buy' => $priceBuy,
                        'base_price_sell' => $priceSell,
                        'min_stock' => $productData['min_stock'] ?? 5,
                        'is_active' => true,
                        'active_flag' => 'Y',
                    ]
                );

                $stock = (float) ($productData['stock'] ?? 0);
                if ($stock <= 0) {
                    continue;
                }

                ProductLocation::updateOrCreate(
                    [
                        'item_uuid' => $product->uuid,
                        'source_type' => 'location',
                        'source_id' => $branchUuid,
                    ],
                    [
                        'uuid' => (string) Str::uuid(),
                        'branch_uuid' => $branchUuid,
                        'company_uuid' => $companyUuid,
                        'on_hand_qty' => $stock,
                        'reserved_qty' => 0,
                        'available_qty' => $stock,
                    ]
                );
            }
        });

        $this->command->info('  Products seeded: '.Product::count());
    }
}
