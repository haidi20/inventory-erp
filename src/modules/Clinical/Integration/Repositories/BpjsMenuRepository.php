<?php

namespace App\Modules\Clinical\Integration\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BpjsMenuRepository
{
    public function getOverview(): array
    {
        $settings = $this->getSettings();
        $filled = collect([
            $settings['base_url'] ?? null,
            $settings['consumer_uuid'] ?? null,
            $settings['user_key'] ?? null,
            $settings['secret_key'] ?? null,
            $settings['ppk_code'] ?? null,
        ])->filter(fn ($value) => filled($value))->count();

        $apiReady = $filled >= 4 && (bool) ($settings['is_active'] ?? false);

        return [
            'isApiReady' => $apiReady,
            'settings' => $settings,
            'completion' => [
                'filled' => $filled,
                'total' => 5,
            ],
            'modules' => [
                ['label' => 'Pendaftaran Peserta', 'ready' => $apiReady],
                ['label' => 'Verifikasi Kepesertaan', 'ready' => $apiReady],
                ['label' => 'Rujukan Online', 'ready' => $apiReady],
                ['label' => 'SEP dan Klaim', 'ready' => $apiReady],
            ],
        ];
    }

    private function getSettings(): array
    {
        if (Schema::hasTable('bpjs_settings')) {
            $row = DB::table('bpjs_settings')->where('id', 1)->first();

            if ($row) {
                return [
                    'base_url' => $row->base_url,
                    'consumer_uuid' => $row->consumer_uuid,
                    'user_key' => $row->user_key,
                    'secret_key' => $row->secret_key,
                    'ppk_code' => $row->ppk_code,
                    'is_active' => (bool) $row->is_active,
                ];
            }
        }

        return [
            'base_url' => config('services.bpjs.base_url'),
            'consumer_uuid' => config('services.bpjs.consumer_uuid'),
            'user_key' => config('services.bpjs.user_key'),
            'secret_key' => config('services.bpjs.secret_key'),
            'ppk_code' => config('services.bpjs.ppk_code'),
            'is_active' => (bool) config('services.bpjs.is_active', false),
        ];
    }
}
