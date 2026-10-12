<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PrepareProductFormE2eData extends Command
{
    protected $signature = 'e2e:product-form-fixtures {config?}';
    protected $description = 'Menyiapkan fixture warehouse dan price level untuk E2E form produk';

    public function handle(): int
    {
        $argument = $this->argument('config');
        if ($argument) {
            $config = json_decode(base64_decode((string) $argument), true, 512, JSON_THROW_ON_ERROR);
        } else {
            $userCompany = DB::table('user_companies')->join('users', 'users.uuid', '=', 'user_companies.user_uuid')->where('users.email', 'superadmin@klinik.local')->where('user_companies.is_default', true)->first();
            $userBranch = DB::table('user_branches')->where('user_uuid', $userCompany?->user_uuid ?? '')->where('is_default', true)->first();
            $config = ['location_uuid' => (string) Str::uuid(), 'price_level_uuid' => (string) Str::uuid(), 'branch_uuid' => $userBranch?->branch_uuid ?? '', 'company_uuid' => $userCompany?->company_uuid ?? ''];
        }
        $now = now()->timestamp;
        DB::table('locations')->where('code', 'E2E-FRM-WH')->delete();
        DB::table('price_levels')->where('code', 'E2E-FRM-PL')->delete();
        DB::table('units')->where('code', 'E2E-UNIT-FRM')->delete();
        DB::table('locations')->insert([
            'uuid' => $config['location_uuid'], 'branch_uuid' => $config['branch_uuid'], 'company_uuid' => $config['company_uuid'],
            'type' => 'warehouse', 'code' => 'E2E-FRM-WH', 'name' => 'E2E Warehouse Form', 'is_active' => true,
            'can_sell' => true, 'is_primary' => false, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => 0,
        ]);
        DB::table('price_levels')->insert([
            'uuid' => $config['price_level_uuid'], 'branch_uuid' => $config['branch_uuid'], 'company_uuid' => $config['company_uuid'],
            'code' => 'E2E-FRM-PL', 'name' => 'E2E Price Level Form', 'is_active' => true, 'created_at' => $now,
            'updated_at' => $now, 'deleted_at' => 0,
        ]);
        DB::table('units')->insert([
            'uuid' => (string) Str::uuid(), 'branch_uuid' => $config['branch_uuid'], 'company_uuid' => $config['company_uuid'],
            'code' => 'E2E-UNIT-FRM', 'name' => 'E2E Unit Form', 'is_active' => true,
            'created_at' => $now, 'updated_at' => $now, 'deleted_at' => 0,
        ]);
        return self::SUCCESS;
    }
}
