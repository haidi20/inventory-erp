<?php

namespace Database\Seeders\V1;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Database\Seeder;

class RuntimeCompanySeeder extends Seeder
{
    public const SANDBOX_COMPANY_UUID = '11111111-1111-4111-8111-111111111111';
    public const PRODUCTION_COMPANY_UUID = '22222222-2222-4222-8222-222222222222';
    public const DEMO_BRANCH_SHORT_NAME = 'KDM';
    public const SANDBOX_BRANCH_SHORT_NAME = 'KSB';
    public const PRODUCTION_BRANCH_SHORT_NAME = 'KPR';

    public function run(): void
    {
        $sandboxCompany = $this->seedCompany(
            uuid: self::SANDBOX_COMPANY_UUID,
            name: 'Klinik Sandbox Testing',
            email: 'sandbox@klinik.local',
            city: 'Makassar',
            address: 'Lingkungan Sandbox / Testing',
        );

        $this->seedBranch(
            shortName: self::SANDBOX_BRANCH_SHORT_NAME,
            name: 'Cabang Sandbox Testing',
            companyUuid: $sandboxCompany->uuid,
            description: 'Cabang khusus sandbox, staging, dan testing otomatis.',
        );

        $productionCompany = $this->seedCompany(
            uuid: self::PRODUCTION_COMPANY_UUID,
            name: 'Klinik Production',
            email: 'production@klinik.local',
            city: 'Makassar',
            address: 'Lingkungan Production',
        );

        $this->seedBranch(
            shortName: self::PRODUCTION_BRANCH_SHORT_NAME,
            name: 'Cabang Production',
            companyUuid: $productionCompany->uuid,
            description: 'Cabang utama untuk data production.',
        );
    }

    private function seedCompany(
        string $uuid,
        string $name,
        string $email,
        string $city,
        string $address,
    ): Company {
        return Company::query()->updateOrCreate(
            ['uuid' => $uuid],
            [
                'name' => $name,
                'clinic_type' => 'klinik pratama',
                'phone' => '0411-000000',
                'email' => $email,
                'address' => $address,
                'city' => $city,
                'is_active' => true,
            ],
        );
    }

    private function seedBranch(
        string $shortName,
        string $name,
        string $companyUuid,
        string $description,
    ): Branch {
        return Branch::query()->updateOrCreate(
            ['short_name' => $shortName],
            [
                'name' => $name,
                'description' => $description,
                'company_uuid' => $companyUuid,
                'is_active' => true,
            ],
        );
    }
}
