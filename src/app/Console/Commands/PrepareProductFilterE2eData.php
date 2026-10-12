<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PrepareProductFilterE2eData extends Command
{
    protected $signature = 'e2e:product-filter-fixtures {config}';

    protected $description = 'Menyiapkan fixture produk lintas perusahaan dan pengguna untuk E2E filter produk';

    public function handle(): int
    {
        $config = json_decode(base64_decode((string) $this->argument('config')), true, 512, JSON_THROW_ON_ERROR);
        $now = now()->timestamp;
        $branchUuid = DB::table('branches')
            ->where('company_uuid', $config['companyA'])
            ->where('deleted_at', 0)
            ->value('uuid');

        if (!$branchUuid) {
            throw new \RuntimeException('Cabang aktif untuk fixture produk E2E tidak ditemukan.');
        }

        DB::table('items')
            ->where('code', 'like', 'AAA-E2E-FILTER-%')
            ->delete();

        foreach ([
            [
                'suffix' => 'A',
                'company_uuid' => $config['companyA'],
                'branch_uuid' => $branchUuid,
                'created_by' => $config['currentUser'],
                'updated_by' => $config['currentUser'],
                'barcode' => 'E2E-BARCODE-A',
                'description' => 'E2E Description A',
                'unit' => 'E2E Unit A',
                'base_unit_uuid' => $config['unitA'],
                'kind' => $config['kindA'],
                'group_uuid' => $config['groupA'],
                'type_uuid' => $config['typeA'],
                'brand_uuid' => $config['brandA'],
                'origin_uuid' => $config['originA'],
                'grade_uuid' => $config['gradeA'],
            ],
            [
                'suffix' => 'B',
                'company_uuid' => $config['companyB'],
                'branch_uuid' => $branchUuid,
                'created_by' => $config['otherUser'],
                'updated_by' => $config['otherUser'],
                'barcode' => 'E2E-BARCODE-B',
                'description' => 'E2E Description B',
                'unit' => 'E2E Unit B',
                'base_unit_uuid' => $config['unitB'],
                'kind' => $config['kindB'],
                'group_uuid' => $config['groupB'],
                'type_uuid' => $config['typeB'],
                'brand_uuid' => $config['brandB'],
                'origin_uuid' => $config['originB'],
                'grade_uuid' => $config['gradeB'],
            ],
        ] as $fixture) {
            DB::table('items')->insert([
                'uuid' => (string) Str::uuid(),
                'branch_uuid' => $fixture['branch_uuid'],
                'company_uuid' => $fixture['company_uuid'],
                'code' => 'AAA-E2E-FILTER-'.$fixture['suffix'],
                'name' => 'E2E Filter Name '.$fixture['suffix'],
                'description' => $fixture['description'],
                'note' => 'E2E Remarks '.$fixture['suffix'],
                'barcode' => $fixture['barcode'],
                'item_type' => 'goods',
                'base_unit_uuid' => $fixture['base_unit_uuid'],
                'item_group_lookup_uuid' => $fixture['group_uuid'],
                'item_type_lookup_uuid' => $fixture['type_uuid'],
                'brand_lookup_uuid' => $fixture['brand_uuid'],
                'origin_lookup_uuid' => $fixture['origin_uuid'],
                'grade_lookup_uuid' => $fixture['grade_uuid'],
                'unit' => $fixture['unit'],
                'kind' => $fixture['kind'],
                'price_buy' => 1000,
                'price_sell' => 2000,
                'base_price_buy' => 1000,
                'base_price_sell' => 2000,
                'min_stock' => 0,
                'max_stock' => 100,
                'is_active' => true,
                'active_flag' => 'Y',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => 0,
                'created_by' => $fixture['created_by'],
                'updated_by' => $fixture['updated_by'],
            ]);
        }

        return self::SUCCESS;
    }
}
