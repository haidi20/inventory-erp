<?php

namespace Database\Seeders\V1;

use App\Modules\Operational\Inventory\Models\StockMovement;
use App\Modules\Operational\Procurement\Models\PurchaseItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StockMovementDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== Stock Movement Data Seeder ===');

        $now = time();
        $stats = 0;

        PurchaseItem::query()->chunkById(100, function ($items) use ($now, &$stats) {
            foreach ($items as $item) {
                $exists = StockMovement::where('reference_type', 'purchase')
                    ->where('reference_uuid', $item->purchase_uuid)
                    ->where('product_uuid', $item->product_uuid)
                    ->exists();

                if ($exists) {
                    continue;
                }

                StockMovement::create([
                    'uuid' => (string) Str::uuid(),
                    'branch_uuid' => $item->branch_uuid,
                    'company_uuid' => $item->company_uuid,
                    'product_uuid' => $item->product_uuid,
                    'qty' => $item->qty,
                    'type' => 'in',
                    'reference_type' => 'purchase',
                    'reference_uuid' => $item->purchase_uuid,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $stats++;
            }
        });

        $this->command->info("  Stock Movements: {$stats} inserted");
        $this->command->info('=== Stock Movement Data Seeder DONE ===');
    }
}
