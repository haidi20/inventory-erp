<?php

namespace App\Modules\Core\Platform\Entities;

/**
 * SettingsEntity
 * 
 * Entitas untuk mengelola pengaturan sistem, profil klinik, dan konfigurasi SATUSEHAT.
 */
class SettingsEntity
{
    public function __construct(
        public ?string $company_name = null,
        public ?string $org_id = null,
        public ?string $client_id = null,
        public ?string $client_secret = null,
        public ?string $base_url = null,
        public ?string $auth_url = null,
        public bool $is_satusehat_active = false,
        public ?string $actor_uuid = null
    ) {}
}

/**
 * Icd10Entity
 * 
 * Entitas untuk mengelola master data kode diagnosa ICD-10.
 */
class Icd10Entity
{
    public function __construct(
        public ?int $id = null,
        public ?string $code = null,
        public ?string $name_id = null,
        public ?string $name_en = null,
        public ?string $category = null,
        public ?string $search = null,
        public int $page = 1,
        public int $per_page = 20,
        public ?string $actor_uuid = null
    ) {}
}
