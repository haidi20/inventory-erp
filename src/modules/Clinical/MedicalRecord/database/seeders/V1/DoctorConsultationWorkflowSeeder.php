<?php

namespace Database\Seeders\V1;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Clinical\MedicalRecord\Models\EncounterItem;
use App\Modules\Clinical\Ancillary\Models\LabOrder;
use App\Modules\Clinical\Ancillary\Models\LabOrderItem;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Clinical\Ancillary\Models\RadiologyOrder;
use App\Modules\Clinical\Registration\Models\Registration;
use App\Modules\Operational\Master\Models\Service;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DoctorConsultationWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::query()->where('short_name', 'KDM')->first();
        $companyUuid = $branch?->company_uuid;
        $patients = Patient::query()
            ->when($companyUuid, fn ($query) => $query->where('company_uuid', $companyUuid))
            ->where('is_active', true)
            ->orderBy('name')
            ->take(20)
            ->get();
        $doctors = Employee::query()
            ->when($companyUuid, fn ($query) => $query->where('company_uuid', $companyUuid))
            ->where('role', 'dokter')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        // Kolom legacy company_uuid sudah di-drop dari tabel users;
        // user dihubungkan ke company via pivot user_companies.
        $operator = User::query()
            ->orderBy('created_at')
            ->first();

        if (!$branch || $patients->count() < 8 || $doctors->isEmpty()) {
            $this->command?->warn('Seeder alur pelayanan poli dilewati karena data cabang, pasien, atau dokter belum siap.');
            return;
        }

        $serviceCatalog = [
            'general_consultation' => $this->findService($branch->uuid, ['SRV-POL-001']),
            'specialist_consultation' => $this->findService($branch->uuid, ['SRV-POL-002']),
            'lab' => $this->findService($branch->uuid, ['SRV-LAB-001']),
            'radiology_usg' => $this->findService($branch->uuid, ['SRV-RAD-001']),
            'radiology_xray' => $this->findService($branch->uuid, ['SRV-RAD-002']),
        ];

        $completedLabRegistration = $this->seedCompletedLabCase(
            branchUuid: $branch->uuid,
            companyUuid: $branch->company_uuid,
            patient: $patients[6],
            doctor: $doctors[0],
            consultationService: $serviceCatalog['specialist_consultation'] ?? $serviceCatalog['general_consultation'],
            labService: $serviceCatalog['lab'],
        );

        $completedRadiologyRegistration = $this->seedCompletedRadiologyCase(
            branchUuid: $branch->uuid,
            companyUuid: $branch->company_uuid,
            patient: $patients[7],
            doctor: $doctors[min(1, $doctors->count() - 1)],
            consultationService: $serviceCatalog['specialist_consultation'] ?? $serviceCatalog['general_consultation'],
            radiologyService: $serviceCatalog['radiology_xray'] ?? $serviceCatalog['radiology_usg'],
        );

        $activeRegistrations = [
            $this->resolveRegistration(
                branchUuid: $branch->uuid,
                companyUuid: $branch->company_uuid,
                queueNo: 'A-014',
                patient: $patients[3],
                doctor: $doctors[min(2, $doctors->count() - 1)],
                registeredAt: Carbon::create(2026, 3, 17, 8, 40)->timestamp,
                paymentType: 'bpjs',
                poli: 'Poli Paru',
                notes: 'Sesak napas ringan saat malam dan batuk berulang, riwayat asma sejak usia sekolah.',
                status: 'waiting',
            ),
            $this->resolveRegistration(
                branchUuid: $branch->uuid,
                companyUuid: $branch->company_uuid,
                queueNo: 'A-015',
                patient: $patients[4],
                doctor: $doctors[min(3, $doctors->count() - 1)],
                registeredAt: Carbon::create(2026, 3, 17, 8, 55)->timestamp,
                paymentType: 'mandiri',
                poli: 'Poli Ortopedi',
                notes: 'Nyeri pinggang bawah menjalar ke paha kiri setelah mengangkat barang berat dua hari lalu.',
                status: 'waiting',
            ),
            $this->resolveRegistration(
                branchUuid: $branch->uuid,
                companyUuid: $branch->company_uuid,
                queueNo: 'A-016',
                patient: $patients[5],
                doctor: $doctors[0],
                registeredAt: Carbon::create(2026, 3, 17, 9, 10)->timestamp,
                paymentType: 'bpjs',
                poli: 'Penyakit Dalam',
                notes: 'Kontrol hipertensi, pasien mengeluh pusing berputar dan tekanan darah rumah masih di atas target.',
                status: 'in_progress',
            ),
            $this->resolveRegistration(
                branchUuid: $branch->uuid,
                companyUuid: $branch->company_uuid,
                queueNo: 'A-017',
                patient: $patients[8],
                doctor: $doctors[min(1, $doctors->count() - 1)],
                registeredAt: Carbon::create(2026, 3, 17, 9, 25)->timestamp,
                paymentType: 'mandiri',
                poli: 'Poli Kulit',
                notes: 'Ruam kemerahan dan gatal pada lipatan siku sejak satu minggu, terutama setelah berkeringat.',
                status: 'in_progress',
            ),
        ];

        $this->seedLabOrders(
            completedRegistration: $completedLabRegistration,
            pendingRegistration: $activeRegistrations[1],
            inProgressRegistration: $activeRegistrations[2],
            operatorUuid: $operator?->uuid,
        );

        $this->seedRadiologyOrders(
            completedRegistration: $completedRadiologyRegistration,
            pendingRegistration: $activeRegistrations[0],
            inProgressRegistration: $activeRegistrations[3],
            radiologistUuid: $operator?->uuid,
        );
    }

    private function seedCompletedLabCase(
        string $branchUuid,
        string $companyUuid,
        Patient $patient,
        Employee $doctor,
        ?Service $consultationService,
        ?Service $labService,
    ): Registration {
        $registeredAt = Carbon::create(2026, 3, 16, 9, 20)->timestamp;
        $registration = $this->resolveRegistration(
            branchUuid: $branchUuid,
            companyUuid: $companyUuid,
            queueNo: 'A-018',
            patient: $patient,
            doctor: $doctor,
            registeredAt: $registeredAt,
            paymentType: 'bpjs',
            poli: 'Penyakit Dalam',
            notes: 'Sering haus, buang air kecil lebih sering malam hari, dan hasil gula darah mandiri di rumah cenderung tinggi.',
            status: 'done',
        );

        $encounter = Encounter::query()->updateOrCreate(
            [
                'branch_uuid' => $branchUuid,
                'registration_uuid' => $registration->uuid,
            ],
            [
                'company_uuid' => $companyUuid,
                'patient_uuid' => $patient->uuid,
                'doctor_uuid' => $doctor->uuid,
                'complaint' => 'Sering haus, mudah lelah, dan kesemutan pada telapak kaki sejak dua minggu terakhir.',
                'diagnosis' => 'Diabetes melitus tipe 2 tidak terkontrol',
                'notes' => 'Dokter meninjau hasil gula darah puasa dan HbA1c, lalu menyusun rencana terapi metformin, edukasi diet, dan kontrol ulang dua minggu.',
                'status' => 'done',
                'created_at' => $registeredAt,
                'updated_at' => $registeredAt,
            ]
        );

        $this->syncEncounterItems($encounter, array_filter([$consultationService, $labService]));

        return $registration;
    }

    private function seedCompletedRadiologyCase(
        string $branchUuid,
        string $companyUuid,
        Patient $patient,
        Employee $doctor,
        ?Service $consultationService,
        ?Service $radiologyService,
    ): Registration {
        $registeredAt = Carbon::create(2026, 3, 16, 11, 5)->timestamp;
        $registration = $this->resolveRegistration(
            branchUuid: $branchUuid,
            companyUuid: $companyUuid,
            queueNo: 'A-019',
            patient: $patient,
            doctor: $doctor,
            registeredAt: $registeredAt,
            paymentType: 'mandiri',
            poli: 'Poli Ortopedi',
            notes: 'Nyeri lutut kanan memberat saat naik tangga, disertai kaku sendi pada pagi hari dan bunyi klik saat menekuk.',
            status: 'done',
        );

        $encounter = Encounter::query()->updateOrCreate(
            [
                'branch_uuid' => $branchUuid,
                'registration_uuid' => $registration->uuid,
            ],
            [
                'company_uuid' => $companyUuid,
                'patient_uuid' => $patient->uuid,
                'doctor_uuid' => $doctor->uuid,
                'complaint' => 'Nyeri lutut kanan saat berdiri lama dan saat naik turun tangga selama lebih dari dua minggu.',
                'diagnosis' => 'Osteoartritis genu dextra',
                'notes' => 'Setelah meninjau hasil foto radiologi, dokter menetapkan terapi antiinflamasi, latihan sendi, dan edukasi penurunan beban aktivitas.',
                'status' => 'done',
                'created_at' => $registeredAt,
                'updated_at' => $registeredAt,
            ]
        );

        $this->syncEncounterItems($encounter, array_filter([$consultationService, $radiologyService]));

        return $registration;
    }

    private function seedLabOrders(
        Registration $completedRegistration,
        Registration $pendingRegistration,
        Registration $inProgressRegistration,
        ?string $operatorUuid,
    ): void {
        $completedOrder = LabOrder::query()->updateOrCreate(
            ['order_no' => 'LAB-KDM-20260316-001'],
            [
                'branch_uuid' => $completedRegistration->branch_uuid,
                'company_uuid' => $completedRegistration->company_uuid,
                'patient_uuid' => $completedRegistration->patient_uuid,
                'doctor_uuid' => $completedRegistration->doctor_uuid,
                'ordered_at' => Carbon::create(2026, 3, 16)->timestamp,
                'status' => 'selesai',
                'clinical_notes' => 'Dokter meminta gula darah puasa, HbA1c, dan kreatinin untuk evaluasi kontrol diabetes dan penyesuaian terapi.',
                'resulted_by' => $operatorUuid,
                'resulted_at' => Carbon::create(2026, 3, 16, 10, 10)->timestamp,
            ]
        );

        $this->syncLabOrderItems($completedOrder, [
            [
                'test_name' => 'Gula Darah Puasa',
                'test_code' => 'GDP',
                'result' => '186',
                'unit' => 'mg/dL',
                'normal_range' => '70 - 110',
                'flag' => 'high',
            ],
            [
                'test_name' => 'HbA1c',
                'test_code' => 'HBA1C',
                'result' => '8.4',
                'unit' => '%',
                'normal_range' => '< 6.5',
                'flag' => 'high',
            ],
            [
                'test_name' => 'Kreatinin',
                'test_code' => 'CREA',
                'result' => '1.0',
                'unit' => 'mg/dL',
                'normal_range' => '0.6 - 1.3',
                'flag' => 'normal',
            ],
        ]);

        $pendingOrder = LabOrder::query()->updateOrCreate(
            ['order_no' => 'LAB-KDM-20260317-001'],
            [
                'branch_uuid' => $pendingRegistration->branch_uuid,
                'company_uuid' => $pendingRegistration->company_uuid,
                'patient_uuid' => $pendingRegistration->patient_uuid,
                'doctor_uuid' => $pendingRegistration->doctor_uuid,
                'ordered_at' => Carbon::create(2026, 3, 17)->timestamp,
                'status' => 'pending',
                'clinical_notes' => 'Pasien nyeri pinggang bawah, dokter meminta urinalisis dan darah lengkap untuk menyingkirkan infeksi atau inflamasi.',
                'resulted_by' => null,
                'resulted_at' => null,
            ]
        );

        $this->syncLabOrderItems($pendingOrder, [
            [
                'test_name' => 'Darah Lengkap',
                'test_code' => 'CBC',
                'result' => null,
                'unit' => null,
                'normal_range' => null,
                'flag' => null,
            ],
            [
                'test_name' => 'Urinalisis',
                'test_code' => 'URINE',
                'result' => null,
                'unit' => null,
                'normal_range' => null,
                'flag' => null,
            ],
        ]);

        $processingOrder = LabOrder::query()->updateOrCreate(
            ['order_no' => 'LAB-KDM-20260317-002'],
            [
                'branch_uuid' => $inProgressRegistration->branch_uuid,
                'company_uuid' => $inProgressRegistration->company_uuid,
                'patient_uuid' => $inProgressRegistration->patient_uuid,
                'doctor_uuid' => $inProgressRegistration->doctor_uuid,
                'ordered_at' => Carbon::create(2026, 3, 17)->timestamp,
                'status' => 'proses',
                'clinical_notes' => 'Kontrol hipertensi dengan keluhan pusing berputar, dokter meminta elektrolit dan fungsi ginjal untuk evaluasi terapi lanjutan.',
                'resulted_by' => null,
                'resulted_at' => null,
            ]
        );

        $this->syncLabOrderItems($processingOrder, [
            [
                'test_name' => 'Natrium',
                'test_code' => 'NA',
                'result' => null,
                'unit' => 'mmol/L',
                'normal_range' => '135 - 145',
                'flag' => null,
            ],
            [
                'test_name' => 'Kalium',
                'test_code' => 'K',
                'result' => null,
                'unit' => 'mmol/L',
                'normal_range' => '3.5 - 5.1',
                'flag' => null,
            ],
        ]);
    }

    private function seedRadiologyOrders(
        Registration $completedRegistration,
        Registration $pendingRegistration,
        Registration $inProgressRegistration,
        ?string $radiologistUuid,
    ): void {
        RadiologyOrder::query()->updateOrCreate(
            ['order_no' => 'RAD-KDM-20260316-001'],
            [
                'branch_uuid' => $completedRegistration->branch_uuid,
                'company_uuid' => $completedRegistration->company_uuid,
                'patient_uuid' => $completedRegistration->patient_uuid,
                'doctor_uuid' => $completedRegistration->doctor_uuid,
                'modality' => 'rontgen',
                'body_part' => 'Lutut kanan AP/Lateral',
                'clinical_notes' => 'Nyeri lutut kanan kronis, bunyi klik saat menekuk, evaluasi perubahan degeneratif sendi.',
                'status' => 'selesai',
                'findings' => 'Tampak penyempitan celah sendi kompartemen medial lutut kanan disertai osteofit marginal ringan, tanpa fraktur.',
                'impression' => 'Gambaran sesuai osteoartritis genu dextra derajat ringan sampai sedang.',
                'radiologist_uuid' => $radiologistUuid,
                'ordered_at' => Carbon::create(2026, 3, 16)->timestamp,
                'resulted_at' => Carbon::create(2026, 3, 16, 12, 5)->timestamp,
            ]
        );

        RadiologyOrder::query()->updateOrCreate(
            ['order_no' => 'RAD-KDM-20260317-001'],
            [
                'branch_uuid' => $pendingRegistration->branch_uuid,
                'company_uuid' => $pendingRegistration->company_uuid,
                'patient_uuid' => $pendingRegistration->patient_uuid,
                'doctor_uuid' => $pendingRegistration->doctor_uuid,
                'modality' => 'rontgen',
                'body_part' => 'Thorax PA',
                'clinical_notes' => 'Batuk berulang dan sesak malam, dokter membutuhkan foto thorax untuk menilai kemungkinan infeksi atau hiperinflasi paru.',
                'status' => 'pending',
                'findings' => null,
                'impression' => null,
                'radiologist_uuid' => null,
                'ordered_at' => Carbon::create(2026, 3, 17)->timestamp,
                'resulted_at' => null,
            ]
        );

        RadiologyOrder::query()->updateOrCreate(
            ['order_no' => 'RAD-KDM-20260317-002'],
            [
                'branch_uuid' => $inProgressRegistration->branch_uuid,
                'company_uuid' => $inProgressRegistration->company_uuid,
                'patient_uuid' => $inProgressRegistration->patient_uuid,
                'doctor_uuid' => $inProgressRegistration->doctor_uuid,
                'modality' => 'usg',
                'body_part' => 'Jaringan lunak siku kanan',
                'clinical_notes' => 'Ruam gatal dan bengkak ringan pada lipatan siku, USG diminta untuk menilai keterlibatan jaringan lunak dan edema lokal.',
                'status' => 'proses',
                'findings' => null,
                'impression' => null,
                'radiologist_uuid' => null,
                'ordered_at' => Carbon::create(2026, 3, 17)->timestamp,
                'resulted_at' => null,
            ]
        );
    }

    private function resolveRegistration(
        string $branchUuid,
        string $companyUuid,
        string $queueNo,
        Patient $patient,
        Employee $doctor,
        $registeredAt,
        string $paymentType,
        string $poli,
        string $notes,
        string $status,
    ): Registration {
        return Registration::query()->updateOrCreate(
            [
                'branch_uuid' => $branchUuid,
                'queue_no' => $queueNo,
            ],
            [
                'company_uuid' => $companyUuid,
                'patient_uuid' => $patient->uuid,
                'doctor_uuid' => $doctor->uuid,
                'payment_type' => $paymentType,
                'poli' => $poli,
                'notes' => $notes,
                'status' => $status,
                'registered_at' => $registeredAt,
                'created_at' => $registeredAt,
                'updated_at' => $registeredAt,
            ]
        );
    }

    private function syncEncounterItems(Encounter $encounter, array $services): void
    {
        foreach ($services as $service) {
            EncounterItem::query()->updateOrCreate(
                [
                    'branch_uuid' => $encounter->branch_uuid,
                    'company_uuid' => $encounter->company_uuid,
                    'encounter_uuid' => $encounter->uuid,
                    'item_type' => 'service',
                    'item_uuid' => $service->uuid,
                ],
                [
                    'qty' => 1,
                    'price' => $service->price,
                    'subtotal' => $service->price,
                ]
            );
        }
    }

    private function syncLabOrderItems(LabOrder $order, array $items): void
    {
        foreach ($items as $item) {
            LabOrderItem::query()->updateOrCreate(
                [
                    'lab_order_uuid' => $order->uuid,
                    'test_name' => $item['test_name'],
                ],
                [
                    'test_code' => $item['test_code'],
                    'result' => $item['result'],
                    'unit' => $item['unit'],
                    'normal_range' => $item['normal_range'],
                    'flag' => $item['flag'],
                ]
            );
        }
    }

    private function findService(string $branchUuid, array $codes): ?Service
    {
        $service = Service::query()
            ->where('branch_uuid', $branchUuid)
            ->whereIn('code', $codes)
            ->first();

        if ($service) {
            return $service;
        }

        return Service::query()
            ->where('branch_uuid', $branchUuid)
            ->orderBy('name')
            ->first();
    }
}
