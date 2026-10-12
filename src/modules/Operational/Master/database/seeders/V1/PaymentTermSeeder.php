<?php

namespace Database\Seeders\V1;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\PaymentTerm;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * PaymentTermSeeder - Data dari Ninesoft https://web.ninesoft.id/site/master/termin
 */
class PaymentTermSeeder extends Seeder
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
            ['name' => 'T0',   'description' => '0 Hari (CASH)', 'due_days' => 0,   'is_primary' => false, 'is_active' => true],
            ['name' => '7',    'description' => '7 Hari',        'due_days' => 7,   'is_primary' => false, 'is_active' => true],
            ['name' => '10',   'description' => '10 HARI',       'due_days' => 10,  'is_primary' => false, 'is_active' => true],
            ['name' => '15',   'description' => '15 Hari',       'due_days' => 15,  'is_primary' => false, 'is_active' => true],
            ['name' => '21',   'description' => '21 Hari',       'due_days' => 21,  'is_primary' => false, 'is_active' => false],
            ['name' => '30',   'description' => '30HARI',        'due_days' => 30,  'is_primary' => false, 'is_active' => true],
            ['name' => '45',   'description' => '45HARI',        'due_days' => 45,  'is_primary' => false, 'is_active' => true],
            ['name' => '60',   'description' => '60HARI',        'due_days' => 60,  'is_primary' => false, 'is_active' => true],
            ['name' => '75',   'description' => '75HARI',        'due_days' => 75,  'is_primary' => false, 'is_active' => true],
            ['name' => '90',   'description' => '90HARI',        'due_days' => 90,  'is_primary' => false, 'is_active' => true],
            ['name' => '120',  'description' => '120 Hari',      'due_days' => 120, 'is_primary' => false, 'is_active' => true],
            ['name' => '180',  'description' => '180 Hari',      'due_days' => 180, 'is_primary' => false, 'is_active' => true],
            ['name' => 'T1',   'description' => '',              'due_days' => 1,   'is_primary' => false, 'is_active' => true],
            ['name' => 'T11',  'description' => '5 hari',        'due_days' => 5,   'is_primary' => false, 'is_active' => true],
            ['name' => 'T65',  'description' => '',              'due_days' => 65,  'is_primary' => false, 'is_active' => true],
            ['name' => 'T95',  'description' => '95 Hari',       'due_days' => 95,  'is_primary' => false, 'is_active' => true],
            ['name' => 'T100', 'description' => '100 HARI',      'due_days' => 100, 'is_primary' => false, 'is_active' => true],
        ];

        foreach ($data as $d) {
            PaymentTerm::updateOrCreate(
                ['name' => $d['name'], 'company_uuid' => $companyUuid, 'deleted_at' => 0],
                [
                    'uuid'         => (string) Str::uuid(),
                    'company_uuid' => $companyUuid,
                    'branch_uuid'  => $branchUuid,
                    'description'  => $d['description'],
                    'due_days'     => $d['due_days'],
                    'is_primary'   => $d['is_primary'],
                    'is_active'    => $d['is_active'],
                ]
            );
        }
    }
}