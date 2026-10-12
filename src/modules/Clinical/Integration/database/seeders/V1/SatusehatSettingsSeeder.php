<?php

namespace Database\Seeders\V1;

use App\Modules\Clinical\Integration\Models\SatusehatSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Seeder untuk menginisialisasi pengaturan default SATUSEHAT.
 * Menggunakan kredensial staging/sandbox untuk lingkungan pengembangan.
 */
class SatusehatSettingsSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk konfigurasi SATUSEHAT.
     * Mengambil nilai default dari lingkungan staging/development.
     */
    public function run(): void
    {
        if (! Schema::hasTable('satusehat_settings')) {
            return;
        }

        // Jika sudah ada data, kita tidak menimpa agar tidak merusak konfigurasi yang sudah diinput user
        if (SatusehatSetting::exists()) {
            return;
        }

        SatusehatSetting::create([
            'uuid' => (string) Str::uuid(),
            'org_uuid' => '10000001',
            'client_uuid' => 'satusehat-clinic-client',
            'client_secret' => 'satusehat-clinic-secret',
            'base_url' => 'https://api-satusehat-stg.dto.kemkes.go.id/fhir-r4/v1',
            'auth_url' => 'https://api-satusehat-stg.dto.kemkes.go.id/oauth2/v1/accesstoken',
            'is_active' => false,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }
}
