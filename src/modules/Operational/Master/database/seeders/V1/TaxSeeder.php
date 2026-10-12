<?php

namespace Database\Seeders\V1;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Tax;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * TaxSeeder - Data dari Ninesoft https://web.ninesoft.id/site/master/pajak
 */
class TaxSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RuntimeCompanySeeder::class);

        $branch      = Branch::where('short_name', 'KDM')->first()
            ?? Branch::where('short_name', RuntimeCompanySeeder::SANDBOX_BRANCH_SHORT_NAME)->first();
        $companyUuid = $branch?->company_uuid
            ?: (string) Company::query()->orderBy('id')->value('uuid')
            ?: RuntimeCompanySeeder::SANDBOX_COMPANY_UUID;
        $branchUuid  = $branch?->uuid;

        $data = [
            ['tax_code' => 'INCL',       'percentage' => 11.0,  'is_primary' => false, 'is_active' => true, 'is_pph' => false],
            ['tax_code' => 'INCL 10',    'percentage' => 10.0,  'is_primary' => false, 'is_active' => true, 'is_pph' => false],
            ['tax_code' => 'INCL 11',    'percentage' => 11.0,  'is_primary' => false, 'is_active' => true, 'is_pph' => false],
            ['tax_code' => 'NO PPN',     'percentage' => 0.0,   'is_primary' => false, 'is_active' => true, 'is_pph' => false],
            ['tax_code' => 'PPN',        'percentage' => 12.0,  'is_primary' => true,  'is_active' => true, 'is_pph' => false],
            ['tax_code' => 'PPN 11',     'percentage' => 11.0,  'is_primary' => false, 'is_active' => true, 'is_pph' => false],
            ['tax_code' => 'ppn 12',     'percentage' => 12.0,  'is_primary' => false, 'is_active' => true, 'is_pph' => false],
            ['tax_code' => 'PPN10',      'percentage' => 10.0,  'is_primary' => false, 'is_active' => true, 'is_pph' => false],
            ['tax_code' => 'PPN13Test',  'percentage' => 13.0,  'is_primary' => false, 'is_active' => true, 'is_pph' => false],
        ];

        foreach ($data as $d) {
            Tax::updateOrCreate(
                ['tax_code' => $d['tax_code'], 'deleted_at' => 0],
                [
                    'uuid'         => (string) Str::uuid(),
                    'company_uuid' => $companyUuid,
                    'branch_uuid'  => $branchUuid,
                    'percentage'   => $d['percentage'],
                    'is_primary'   => $d['is_primary'],
                    'is_active'    => $d['is_active'],
                    'is_pph'       => $d['is_pph'],
                ]
            );
        }
    }
}