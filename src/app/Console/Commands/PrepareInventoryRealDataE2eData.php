<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PrepareInventoryRealDataE2eData extends Command
{
    protected $signature = 'e2e:inventory-real-data-fixtures {--cleanup}';

    protected $description = 'Menyiapkan atau membersihkan data nyata untuk E2E inventory';

    public function handle(): int
    {
        $this->cleanup();

        if ($this->option('cleanup')) {
            return self::SUCCESS;
        }

        $userCompany = DB::table('user_companies')
            ->join('users', 'users.uuid', '=', 'user_companies.user_uuid')
            ->where('users.email', 'superadmin@klinik.local')
            ->where('user_companies.is_default', true)
            ->first();

        $userBranch = DB::table('user_branches')
            ->where('user_uuid', $userCompany?->user_uuid ?? '')
            ->where('is_default', true)
            ->first();

        if (!$userCompany || !$userBranch) {
            throw new RuntimeException('Perusahaan atau cabang default E2E tidak ditemukan.');
        }

        $location = DB::table('locations')
            ->where('branch_uuid', $userBranch->branch_uuid)
            ->where('deleted_at', 0)
            ->orderByDesc('is_primary')
            ->first();

        if (!$location) {
            $location = DB::table('locations')
                ->where('company_uuid', $userCompany->company_uuid)
                ->where('deleted_at', 0)
                ->first();
        }

        if (!$location) {
            throw new RuntimeException('Lokasi aktif untuk fixture inventory E2E tidak ditemukan.');
        }

        $now = now()->timestamp;
        $itemUuid = (string) Str::uuid();
        $itemStockUuid = (string) Str::uuid();
        $packageUuid = (string) Str::uuid();
        $packageItemUuid = (string) Str::uuid();

        DB::table('items')->insert([
            'uuid' => $itemUuid,
            'branch_uuid' => $userBranch->branch_uuid,
            'company_uuid' => $userCompany->company_uuid,
            'code' => 'E2E-INV-REAL-ITEM',
            'name' => 'E2E Real Data Opening Barcode',
            'barcode' => 'E2E-INV-REAL-BARCODE',
            'item_type' => 'goods',
            'unit' => 'Box',
            'price_buy' => 5000,
            'price_sell' => 7500,
            'base_price_buy' => 5000,
            'base_price_sell' => 7500,
            'min_stock' => 1,
            'max_stock' => 100,
            'is_active' => true,
            'active_flag' => 'Y',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => 0,
            'created_by' => $userCompany->user_uuid,
            'updated_by' => $userCompany->user_uuid,
        ]);

        DB::table('item_stocks')->insert([
            'uuid' => $itemStockUuid,
            'branch_uuid' => $userBranch->branch_uuid,
            'company_uuid' => $userCompany->company_uuid,
            'item_uuid' => $itemUuid,
            'source_type' => 'location',
            'source_id' => $location->uuid,
            'location_uuid' => $location->uuid,
            'on_hand_qty' => 25,
            'reserved_qty' => 0,
            'available_qty' => 25,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => 0,
            'created_by' => $userCompany->user_uuid,
            'updated_by' => $userCompany->user_uuid,
        ]);

        DB::table('service_packages')->insert([
            'uuid' => $packageUuid,
            'branch_uuid' => $userBranch->branch_uuid,
            'company_uuid' => $userCompany->company_uuid,
            'code' => 'E2E-INV-REAL-BOM',
            'name' => 'E2E Real Data BOM Formula',
            'price' => 15000,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => 0,
            'created_by' => $userCompany->user_uuid,
            'updated_by' => $userCompany->user_uuid,
        ]);

        DB::table('service_package_items')->insert([
            'uuid' => $packageItemUuid,
            'branch_uuid' => $userBranch->branch_uuid,
            'company_uuid' => $userCompany->company_uuid,
            'service_package_uuid' => $packageUuid,
            'item_type' => 'product',
            'item_uuid' => $itemUuid,
            'qty' => 2,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => 0,
            'created_by' => $userCompany->user_uuid,
            'updated_by' => $userCompany->user_uuid,
        ]);

        return self::SUCCESS;
    }

    private function cleanup(): void
    {
        $itemUuids = DB::table('items')
            ->where('code', 'like', 'E2E-INV-REAL-%')
            ->pluck('uuid');

        if ($itemUuids->isNotEmpty()) {
            DB::table('item_stocks')->whereIn('item_uuid', $itemUuids)->delete();
            DB::table('item_opening_balances')->whereIn('item_uuid', $itemUuids)->delete();
            DB::table('items')->whereIn('uuid', $itemUuids)->delete();
        }

        $packageUuids = DB::table('service_packages')
            ->where('code', 'like', 'E2E-INV-REAL-%')
            ->pluck('uuid');

        if ($packageUuids->isNotEmpty()) {
            DB::table('service_package_items')->whereIn('service_package_uuid', $packageUuids)->delete();
            DB::table('service_packages')->whereIn('uuid', $packageUuids)->delete();
        }
    }
}
