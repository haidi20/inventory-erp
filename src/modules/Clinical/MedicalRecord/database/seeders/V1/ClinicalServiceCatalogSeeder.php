<?php

namespace Database\Seeders\V1;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Models\Service;
use App\Modules\Operational\Master\Models\ServicePackage;
use App\Modules\Operational\Master\Models\ServicePackageItem;
use Illuminate\Database\Seeder;

class ClinicalServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RuntimeCompanySeeder::class);

        $sandboxBranch = Branch::query()
            ->where('short_name', RuntimeCompanySeeder::SANDBOX_BRANCH_SHORT_NAME)
            ->firstOrFail();

        $branch = Branch::query()->updateOrCreate(
            ['short_name' => 'KDM'],
            [
                'name' => 'Klinik Demo',
                'company_uuid' => $sandboxBranch->company_uuid,
                'is_active' => true,
            ]
        );

        $services = [
            [
                'code' => 'SRV-POL-001',
                'name' => 'Konsultasi Dokter Umum',
                'duration_minutes' => 20,
                'price' => 125000,
                'doctor_fee' => 30000,
            ],
            [
                'code' => 'SRV-POL-002',
                'name' => 'Konsultasi Dokter Spesialis',
                'duration_minutes' => 30,
                'price' => 200000,
                'doctor_fee' => 60000,
            ],
            [
                'code' => 'SRV-POL-003',
                'name' => 'Pemeriksaan Koreksi Visus',
                'duration_minutes' => 25,
                'price' => 85000,
                'doctor_fee' => 20000,
            ],
            [
                'code' => 'SRV-LAB-001',
                'name' => 'Pemeriksaan Laboratorium Dasar',
                'duration_minutes' => 40,
                'price' => 175000,
                'doctor_fee' => 0,
            ],
            [
                'code' => 'SRV-RAD-001',
                'name' => 'Pemeriksaan Radiologi USG',
                'duration_minutes' => 45,
                'price' => 275000,
                'doctor_fee' => 40000,
            ],
            [
                'code' => 'SRV-RAD-002',
                'name' => 'Pemeriksaan Radiologi Rontgen',
                'duration_minutes' => 30,
                'price' => 225000,
                'doctor_fee' => 30000,
            ],
            [
                'code' => 'SRV-FAR-001',
                'name' => 'Konseling Farmasi dan Edukasi Obat',
                'duration_minutes' => 15,
                'price' => 50000,
                'doctor_fee' => 0,
            ],
            [
                'code' => 'SRV-MCU-001',
                'name' => 'Tes Visus dan Buta Warna',
                'duration_minutes' => 20,
                'price' => 90000,
                'doctor_fee' => 15000,
            ],
            [
                'code' => 'SRV-MCU-002',
                'name' => 'Spirometry / Faal Paru',
                'duration_minutes' => 30,
                'price' => 175000,
                'doctor_fee' => 25000,
            ],
            [
                'code' => 'SRV-MCU-003',
                'name' => 'Treadmill Stress Test',
                'duration_minutes' => 60,
                'price' => 450000,
                'doctor_fee' => 75000,
            ],
            [
                'code' => 'SRV-MCU-004',
                'name' => 'Audiometry Nada Murni',
                'duration_minutes' => 30,
                'price' => 150000,
                'doctor_fee' => 20000,
            ],
        ];

        foreach ($services as $service) {
            Service::query()->updateOrCreate(
                [
                    'branch_uuid' => $branch->uuid,
                    'code' => $service['code'],
                ],
                $service + ['is_active' => true, 'company_uuid' => $branch->company_uuid]
            );
        }

        $medicalCheckupPackage = ServicePackage::query()->updateOrCreate(
            [
                'branch_uuid' => $branch->uuid,
                'code' => 'PKG-MCU-001',
            ],
            [
                'name' => 'Medical Check-Up Fungsional',
                'price' => 775000,
                'is_active' => true,
                'company_uuid' => $branch->company_uuid,
            ]
        );

        foreach (['SRV-MCU-001', 'SRV-MCU-002', 'SRV-MCU-003', 'SRV-MCU-004'] as $serviceCode) {
            $service = Service::query()
                ->where('branch_uuid', $branch->uuid)
                ->where('code', $serviceCode)
                ->first();

            if (!$service) {
                continue;
            }

            ServicePackageItem::query()->updateOrCreate(
                [
                    'branch_uuid' => $branch->uuid,
                    'service_package_uuid' => $medicalCheckupPackage->uuid,
                    'item_type' => 'service',
                    'item_uuid' => $service->uuid,
                ],
                ['qty' => 1, 'company_uuid' => $branch->company_uuid]
            );
        }
    }
}
