<?php

namespace Database\Seeders\V1;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Supplier;
use App\Modules\Operational\Procurement\Models\Purchase;
use App\Modules\Operational\Procurement\Models\PurchaseItem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class PurchaseDataSeeder extends Seeder
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
        $this->command->info('=== Purchase Data Seeder ===');

        $existingCount = Purchase::count();
        if ($existingCount >= 50) {
            $this->command->info("  Purchases already seeded ({$existingCount}). Skipping.");

            return;
        }

        $branch = Branch::orderBy('id')->first();
        if (! $branch) {
            throw new RuntimeException('Purchase data seeder requires at least one branch.');
        }

        $purchases = $this->loadJson('operational_purchases.json');
        $invoiceItems = $this->loadJson('operational_invoice_items.json');
        if (! $purchases) {
            return;
        }

        $supplierMap = Supplier::pluck('uuid', 'name')->toArray();
        $fuzzyMap = [];
        foreach ($supplierMap as $name => $uuid) {
            $fuzzyMap[strtolower(substr($name, 0, 15))] = $uuid;
        }

        $productsByName = Product::get(['uuid', 'name'])
            ->mapWithKeys(fn ($product) => [strtolower(trim($product->name)) => $product->uuid])
            ->toArray();

        $stats = ['purchases' => 0, 'items' => 0];

        Purchase::transaction(function () use ($purchases, $invoiceItems, $supplierMap, $fuzzyMap, $productsByName, $branch, &$stats) {
            foreach ($purchases as $data) {
                $supplierUuid = $supplierMap[$data['supplier']]
                    ?? $fuzzyMap[strtolower(substr($data['supplier'], 0, 15))]
                    ?? null;

                if (! $supplierUuid || Purchase::where('invoice_no', $data['invoice_no'])->exists()) {
                    continue;
                }

                $purchaseDate = Carbon::parse($data['purchase_date']);
                $purchase = Purchase::create([
                    'uuid' => (string) Str::uuid(),
                    'branch_uuid' => $branch->uuid,
                    'company_uuid' => $branch->company_uuid,
                    'invoice_no' => $data['invoice_no'],
                    'supplier_uuid' => $supplierUuid,
                    'total' => $data['total'],
                    'paid' => $data['paid'],
                    'status' => $data['status'],
                    'purchase_date' => $purchaseDate->timestamp,
                    'created_at' => $purchaseDate->copy()->setTime(10, 0)->timestamp,
                    'updated_at' => $purchaseDate->copy()->setTime(10, 0)->timestamp,
                ]);
                $stats['purchases']++;

                $lineItems = $invoiceItems[$data['invoice_no']] ?? [];
                $lineCount = count($lineItems) ?: 1;

                foreach ($lineItems as $item) {
                    $productUuid = $productsByName[strtolower(trim($item['name']))] ?? null;
                    if (! $productUuid) {
                        continue;
                    }

                    $price = $item['price'] ?: 0;
                    $qty = 1;
                    if ($price > 0) {
                        $estimated = ($data['total'] / $lineCount) / $price;
                        $qty = max(1, (int) round($estimated));
                    }

                    PurchaseItem::create([
                        'uuid' => (string) Str::uuid(),
                        'branch_uuid' => $branch->uuid,
                        'company_uuid' => $branch->company_uuid,
                        'purchase_uuid' => $purchase->uuid,
                        'product_uuid' => $productUuid,
                        'qty' => $qty,
                        'price' => $price,
                        'subtotal' => $price * $qty,
                        'created_at' => $purchaseDate->timestamp,
                        'updated_at' => $purchaseDate->timestamp,
                    ]);
                    $stats['items']++;
                }
            }
        });

        $this->command->info("  Purchases: {$stats['purchases']} inserted");
        $this->command->info("  Purchase Items: {$stats['items']} inserted");
        $this->command->info('=== Purchase Data Seeder DONE ===');
    }
}
