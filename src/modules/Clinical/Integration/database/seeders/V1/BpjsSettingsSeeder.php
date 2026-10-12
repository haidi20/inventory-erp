<?php

namespace Database\Seeders\V1;

use App\Modules\Clinical\Integration\Models\BpjsSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BpjsSettingsSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('bpjs_settings')) {
            return;
        }

        if (BpjsSetting::exists()) {
            return;
        }

        BpjsSetting::create([
            'uuid' => (string) Str::uuid(),
            'base_url' => config('services.bpjs.base_url', 'https://apijkn-dev.bpjs-kesehatan.go.id/vclaim-rest'),
            'consumer_uuid' => null,
            'user_key' => null,
            'secret_key' => null,
            'ppk_code' => null,
            'is_active' => false,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }
}
