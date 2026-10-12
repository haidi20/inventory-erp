<?php

namespace Database\Seeders\V1;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * UnitSeeder
 * Data bersumber dari Ninesoft Accounting Online + satuan klinik tambahan
 * https://web.ninesoft.id/site/master/satuan
 */
class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $branch      = Branch::where('short_name', 'KDM')->first()
            ?? Branch::where('short_name', RuntimeCompanySeeder::SANDBOX_BRANCH_SHORT_NAME)->first();
        $companyUuid = $branch?->company_uuid
            ?: (string) Company::query()->orderBy('id')->value('uuid')
            ?: RuntimeCompanySeeder::SANDBOX_COMPANY_UUID;

        $data = [
            ['code' => 'BATANG', 'name' => 'BATANG', 'is_active' => true, 'is_primary' => false],
            ['code' => 'BIJI', 'name' => 'BIJI', 'is_active' => true, 'is_primary' => false],
            ['code' => 'BKS', 'name' => 'BUNGKUS', 'is_active' => true, 'is_primary' => false],
            ['code' => 'BKS20', 'name' => 'BKS20', 'is_active' => true, 'is_primary' => false],
            ['code' => 'BTL', 'name' => 'BOTOL', 'is_active' => true, 'is_primary' => false],
            ['code' => 'CM', 'name' => 'CM', 'is_active' => true, 'is_primary' => false],
            ['code' => 'DOS', 'name' => 'DOS', 'is_active' => true, 'is_primary' => false],
            ['code' => 'DRUM', 'name' => 'DRUM', 'is_active' => true, 'is_primary' => false],
            ['code' => 'Gram', 'name' => 'Gram', 'is_active' => true, 'is_primary' => false],
            ['code' => 'IKAT', 'name' => 'IKAT', 'is_active' => true, 'is_primary' => false],
            ['code' => 'JAM', 'name' => 'JAM', 'is_active' => true, 'is_primary' => false],
            ['code' => 'KANTONG', 'name' => 'KANTONG', 'is_active' => true, 'is_primary' => false],
            ['code' => 'Karton', 'name' => '1 Karton isi 12', 'is_active' => true, 'is_primary' => false],
            ['code' => 'KG', 'name' => 'KG', 'is_active' => true, 'is_primary' => false],
            ['code' => 'KLG', 'name' => 'KLG', 'is_active' => true, 'is_primary' => false],
            ['code' => 'KRG', 'name' => 'KARUNG', 'is_active' => true, 'is_primary' => false],
            ['code' => 'KTK', 'name' => 'KOTAK', 'is_active' => true, 'is_primary' => false],
            ['code' => 'LBR', 'name' => 'LEMBAR', 'is_active' => true, 'is_primary' => false],
            ['code' => 'LSN', 'name' => 'LUSIN', 'is_active' => true, 'is_primary' => false],
            ['code' => 'LTR', 'name' => 'LITER', 'is_active' => true, 'is_primary' => false],
            ['code' => 'METER', 'name' => 'METER', 'is_active' => true, 'is_primary' => false],
            ['code' => 'MILI', 'name' => 'MILI', 'is_active' => true, 'is_primary' => false],
            ['code' => 'ML', 'name' => 'ML', 'is_active' => true, 'is_primary' => false],
            ['code' => 'PACK', 'name' => 'PACK', 'is_active' => true, 'is_primary' => false],
            ['code' => 'PACK/5', 'name' => 'PACK/5', 'is_active' => true, 'is_primary' => false],
            ['code' => 'PACKS', 'name' => 'PACKS', 'is_active' => true, 'is_primary' => false],
            ['code' => 'PAIL', 'name' => 'PAIL', 'is_active' => true, 'is_primary' => false],
            ['code' => 'PCS', 'name' => 'Pieces', 'is_active' => true, 'is_primary' => false],
            ['code' => 'PCS-C', 'name' => 'PCS-C', 'is_active' => true, 'is_primary' => true],
            ['code' => 'PCS-P', 'name' => 'PCS-P', 'is_active' => true, 'is_primary' => false],
            ['code' => 'PETI', 'name' => 'PETI', 'is_active' => true, 'is_primary' => false],
            ['code' => 'PKT', 'name' => 'PAKET', 'is_active' => true, 'is_primary' => false],
            ['code' => 'RIM', 'name' => 'RIM', 'is_active' => true, 'is_primary' => false],
            ['code' => 'RIT', 'name' => 'RIT', 'is_active' => true, 'is_primary' => false],
            ['code' => 'ROLL', 'name' => 'ROLL', 'is_active' => true, 'is_primary' => false],
            ['code' => 'SAK', 'name' => 'SAK', 'is_active' => true, 'is_primary' => false],
            ['code' => 'SET', 'name' => 'SET', 'is_active' => true, 'is_primary' => false],
            ['code' => 'SHEETS', 'name' => 'SHEETS', 'is_active' => true, 'is_primary' => false],
            ['code' => 'TANGKI', 'name' => 'TANGKI', 'is_active' => true, 'is_primary' => false],
            ['code' => 'TON', 'name' => 'TON', 'is_active' => true, 'is_primary' => false],
            ['code' => 'UNIT', 'name' => 'UNIT', 'is_active' => true, 'is_primary' => false],
            ['code' => 'TABLET', 'name' => 'Tablet', 'is_active' => true, 'is_primary' => true],
            ['code' => 'KAPSUL', 'name' => 'Kapsul', 'is_active' => true, 'is_primary' => false],
            ['code' => 'STRIP', 'name' => 'Strip', 'is_active' => true, 'is_primary' => false],
            ['code' => 'VIAL', 'name' => 'Vial', 'is_active' => true, 'is_primary' => false],
            ['code' => 'AMPUL', 'name' => 'Ampul', 'is_active' => true, 'is_primary' => false],
            ['code' => 'TUBE', 'name' => 'Tube', 'is_active' => true, 'is_primary' => false],
            ['code' => 'SACHET', 'name' => 'Sachet', 'is_active' => true, 'is_primary' => false],
            ['code' => 'FLASK', 'name' => 'Flask', 'is_active' => true, 'is_primary' => false],
            ['code' => 'MG', 'name' => 'Miligram', 'is_active' => true, 'is_primary' => false],
            ['code' => 'CC', 'name' => 'Cubic Centimeter', 'is_active' => true, 'is_primary' => false],
            ['code' => 'DUS', 'name' => 'Dus', 'is_active' => true, 'is_primary' => false],
            ['code' => 'BOX', 'name' => 'Box', 'is_active' => true, 'is_primary' => false],
            ['code' => 'MENIT', 'name' => 'Menit', 'is_active' => true, 'is_primary' => false],
            ['code' => 'HARI', 'name' => 'Hari', 'is_active' => true, 'is_primary' => false],
        ];

        foreach ($data as $d) {
            Unit::updateOrCreate(
                ['company_uuid' => $companyUuid, 'name' => $d['name'], 'deleted_at' => 0],
                [
                    'uuid'           => (string) Str::uuid(),
                    'code'           => $d['code'],
                    'company_uuid'   => $companyUuid,
                    'name'           => $d['name'],
                    'is_active'      => $d['is_active'],
                    'is_primary'     => $d['is_primary'],
                    'is_base_global' => $d['is_primary'],
                ]
            );
        }
    }
}
