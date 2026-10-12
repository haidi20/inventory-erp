<?php

namespace Database\Seeders\V1;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * WarehouseSeeder - Data dari Ninesoft https://web.ninesoft.id/site/master/gudang
 */
class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RuntimeCompanySeeder::class);

        $branch      = Branch::where('short_name', 'KDM')->first()
            ?? Branch::where('short_name', RuntimeCompanySeeder::SANDBOX_BRANCH_SHORT_NAME)->first();
        $branchUuid  = $branch?->uuid ?? (string) Str::uuid();
        $companyUuid = $branch?->company_uuid
            ?: (string) Company::query()->orderBy('id')->value('uuid')
            ?: RuntimeCompanySeeder::SANDBOX_COMPANY_UUID;

        $data = [
            ['code' => 'PUSAT',            'name' => 'PUSAT',            'description' => '',              'is_primary' => true,  'can_sell' => true,  'is_active' => true],
            ['code' => 'KONSINYASI',       'name' => 'KONSINYASI',       'description' => '',              'is_primary' => false, 'can_sell' => false, 'is_active' => true],
            ['code' => 'KONTAINER',        'name' => 'KONTAINER',        'description' => '',              'is_primary' => false, 'can_sell' => false, 'is_active' => true],
            ['code' => 'KONTAINER A',      'name' => 'KONTAINER A',      'description' => '',              'is_primary' => false, 'can_sell' => false, 'is_active' => true],
            ['code' => 'JAYA MULYA',       'name' => 'JAYA MULYA',       'description' => 'GUDANG A',      'is_primary' => false, 'can_sell' => true,  'is_active' => true],
            ['code' => 'KALIANAK',         'name' => 'KALIANAK',         'description' => '',              'is_primary' => false, 'can_sell' => false, 'is_active' => true],
            ['code' => 'ABADI JAYA',       'name' => 'ABADI JAYA',       'description' => '',              'is_primary' => true,  'can_sell' => true,  'is_active' => true],
            ['code' => 'GD. BARANG PENDING','name' => 'GD. BARANG PENDING','description' => 'GD. BARANG PENDING','is_primary' => false, 'can_sell' => true,  'is_active' => true],
            ['code' => 'Gudang Barang Reject','name' => 'Gudang Barang Reject','description' => '',        'is_primary' => false, 'can_sell' => true,  'is_active' => true],
            ['code' => 'GUDANGBS',         'name' => 'GUDANGBS',         'description' => '',              'is_primary' => false, 'can_sell' => true,  'is_active' => true],
            ['code' => 'PUSAT- TENGAH',    'name' => 'PUSAT- TENGAH',    'description' => '',              'is_primary' => false, 'can_sell' => true,  'is_active' => true],
            ['code' => 'PUSAT-BELAKANG',   'name' => 'PUSAT-BELAKANG',   'description' => '',              'is_primary' => false, 'can_sell' => true,  'is_active' => true],
            ['code' => 'SAMPIT',           'name' => 'SAMPIT',           'description' => '',              'is_primary' => false, 'can_sell' => false, 'is_active' => true],
            ['code' => 'Atom',             'name' => 'Atom',             'description' => '',              'is_primary' => false, 'can_sell' => true,  'is_active' => true],
        ];

        foreach ($data as $d) {
            Location::updateOrCreate(
                ['branch_uuid' => $branchUuid, 'code' => $d['code'], 'deleted_at' => 0],
                [
                    'uuid'         => (string) Str::uuid(),
                    'company_uuid' => $companyUuid,
                    'type'         => 'warehouse',
                    'name'         => $d['name'],
                    'description'  => $d['description'],
                    'is_primary'   => $d['is_primary'],
                    'can_sell'     => $d['can_sell'],
                    'is_active'    => $d['is_active'],
                ]
            );
        }
    }
}