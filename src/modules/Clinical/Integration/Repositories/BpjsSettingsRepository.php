<?php

namespace App\Modules\Clinical\Integration\Repositories;

use App\Modules\Clinical\Integration\Entities\BpjsSettingsEntity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BpjsSettingsRepository
{
    public function get(): object
    {
        if (!Schema::hasTable('bpjs_settings')) {
            return $this->defaultSettings();
        }

        $settings = DB::table('bpjs_settings')->where('id', 1)->first();

        return $settings ?: $this->defaultSettings();
    }

    public function save(BpjsSettingsEntity $entity): void
    {
        DB::beginTransaction();
        try {
            if (!Schema::hasTable('bpjs_settings')) {
                return;
            }

            $data = $entity->toArray();
            $payload = array_merge($this->defaultAttributes(), $data, ['updated_at' => time()]);
            $exists = DB::table('bpjs_settings')->where('id', 1)->exists();

            if ($exists) {
                DB::table('bpjs_settings')->where('id', 1)->update($payload);
                return;
            }

            $payload['id'] = 1;
            DB::table('bpjs_settings')->insert($payload);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function defaultSettings(): object
    {
        return (object) array_merge(['id' => 1], $this->defaultAttributes());
    }

    private function defaultAttributes(): array
    {
        return [
            'base_url' => config('services.bpjs.base_url', 'https://apijkn-dev.bpjs-kesehatan.go.id/vclaim-rest'),
            'consumer_uuid' => config('services.bpjs.consumer_uuid'),
            'user_key' => config('services.bpjs.user_key'),
            'secret_key' => config('services.bpjs.secret_key'),
            'ppk_code' => config('services.bpjs.ppk_code'),
            'is_active' => (bool) config('services.bpjs.is_active', false),
            'created_at' => time(),
            'updated_at' => time(),
        ];
    }
}
