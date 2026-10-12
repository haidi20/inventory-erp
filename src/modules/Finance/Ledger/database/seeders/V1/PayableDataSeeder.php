<?php

namespace Database\Seeders\V1;

use App\Modules\Finance\Ledger\Models\Payable;
use App\Modules\Operational\Procurement\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PayableDataSeeder extends Seeder
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
        $this->command->info('=== Payable Data Seeder ===');

        $purchaseData = collect($this->loadJson('operational_purchases.json'))->keyBy('invoice_no');
        if ($purchaseData->isEmpty()) {
            return;
        }

        $stats = 0;

        Purchase::query()->chunkById(100, function ($purchases) use ($purchaseData, &$stats) {
            foreach ($purchases as $purchase) {
                if (Payable::where('reference_type', 'purchase')
                    ->where('reference_uuid', $purchase->uuid)
                    ->exists()) {
                    continue;
                }

                $data = $purchaseData->get($purchase->invoice_no);
                if (! $data) {
                    continue;
                }

                $purchaseDate = Carbon::parse($data['purchase_date']);
                $dueDate = $data['due_date']
                    ? Carbon::parse($data['due_date'])->timestamp
                    : $purchaseDate->copy()->addDays(30)->timestamp;

                Payable::create([
                    'uuid' => (string) Str::uuid(),
                    'branch_uuid' => $purchase->branch_uuid,
                    'company_uuid' => $purchase->company_uuid,
                    'reference_type' => 'purchase',
                    'reference_uuid' => $purchase->uuid,
                    'amount' => $data['amount_hutang'] ?: $data['total'],
                    'paid' => $data['paid'],
                    'status' => $data['status'],
                    'due_date' => $dueDate,
                    'created_at' => $purchaseDate->copy()->setTime(10, 0)->timestamp,
                    'updated_at' => $purchaseDate->copy()->setTime(10, 0)->timestamp,
                ]);
                $stats++;
            }
        });

        $this->command->info("  Payables: {$stats} inserted");
        $this->command->info('=== Payable Data Seeder DONE ===');
    }
}
