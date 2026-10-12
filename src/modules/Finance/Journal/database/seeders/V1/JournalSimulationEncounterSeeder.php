<?php

namespace Database\Seeders\V1;

use App\Modules\Finance\Journal\Enums\JournalReferenceModel;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Clinical\Registration\Models\Registration;
use App\Modules\Finance\Ledger\Models\Receivable;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class JournalSimulationEncounterSeeder extends Seeder
{
    public function run(): void
    {
        $journalService = app(JournalService::class);
        $branch = Branch::where('short_name', 'KDM')->firstOrFail();
        $companyUuid = $branch->company_uuid;

        $account1201 = Account::where('code', '1201')->firstOrFail();
        $account4101 = Account::where('code', '4101')->firstOrFail();
        $account4102 = Account::where('code', '4102')->firstOrFail();

        $today = Carbon::create(2026, 3, 17);
        $startDate = $today->copy()->subMonth()->day(27);
        $endDate = $today->copy()->day(26);

        $patients = Patient::query()
            ->where('company_uuid', $companyUuid)
            ->where('is_active', true)
            ->orderBy('name')
            ->take(12)
            ->get();

        $doctors = Employee::query()
            ->where('company_uuid', $companyUuid)
            ->where('role', 'dokter')
            ->orderBy('name')
            ->get();

        if ($patients->isEmpty() || $doctors->isEmpty()) {
            $this->command?->warn('Seeder simulasi pelayanan poli dilewati karena data pasien atau dokter belum tersedia.');
            return;
        }

        $visitScenarios = collect([
            [
                'queue_no' => 'A-001',
                'poli' => 'Penyakit Dalam',
                'payment_type' => 'bpjs',
                'complaint' => 'Kontrol diabetes melitus, gula darah puasa di rumah masih tinggi dan kaki kanan terasa kesemutan.',
                'diagnosis' => 'DM Tipe 2',
                'consultation_fee' => 150000,
                'procedure_fee' => 50000,
            ],
            [
                'queue_no' => 'A-002',
                'poli' => 'Poli Umum',
                'payment_type' => 'mandiri',
                'complaint' => 'Batuk berdahak sejak 4 hari, disertai demam ringan pada malam hari and tenggorokan terasa nyeri.',
                'diagnosis' => 'ISPA',
                'consultation_fee' => 150000,
                'procedure_fee' => 50000,
            ],
            [
                'queue_no' => 'A-003',
                'poli' => 'Penyakit Dalam',
                'payment_type' => 'bpjs',
                'complaint' => 'Kontrol hipertensi, pasien mengeluh pusing saat bangun pagi dan tekanan darah di rumah masih tinggi.',
                'diagnosis' => 'Hipertensi',
                'consultation_fee' => 150000,
                'procedure_fee' => 50000,
            ],
            [
                'queue_no' => 'A-004',
                'poli' => 'Poli Umum',
                'payment_type' => 'mandiri',
                'complaint' => 'Nyeri ulu hati sejak 3 hari, terasa perih setelah telat makan dan disertai mual tanpa muntah.',
                'diagnosis' => 'Gastritis',
                'consultation_fee' => 150000,
                'procedure_fee' => 50000,
            ],
            [
                'queue_no' => 'A-005',
                'poli' => 'Poli Umum',
                'payment_type' => 'mandiri',
                'complaint' => 'Pilek, batuk kering, badan meriang, dan hidung tersumbat sejak 2 hari terakhir.',
                'diagnosis' => 'Common Cold',
                'consultation_fee' => 150000,
                'procedure_fee' => 50000,
            ],
            [
                'queue_no' => 'A-006',
                'poli' => 'Poli Kulit',
                'payment_type' => 'mandiri',
                'complaint' => 'Ruam kemerahan dan gatal pada lengan sejak satu minggu, terutama setelah terkena sabun cuci.',
                'diagnosis' => 'Dermatitis',
                'consultation_fee' => 150000,
                'procedure_fee' => 50000,
            ],
            [
                'queue_no' => 'A-007',
                'poli' => 'Penyakit Dalam',
                'payment_type' => 'bpjs',
                'complaint' => 'Kontrol pasca pengobatan, kondisi umum membaik, nafsu makan baik, dan sudah tidak demam.',
                'diagnosis' => 'Kontrol Pasca Terapi',
                'consultation_fee' => 150000,
                'procedure_fee' => 50000,
            ],
            [
                'queue_no' => 'A-008',
                'poli' => 'Poli Ortopedi',
                'payment_type' => 'mandiri',
                'complaint' => 'Nyeri lutut kanan saat berjalan dan naik tangga sejak dua minggu, lebih berat setelah aktivitas.',
                'diagnosis' => 'Osteoartritis',
                'consultation_fee' => 150000,
                'procedure_fee' => 50000,
            ],
            [
                'queue_no' => 'A-009',
                'poli' => 'Poli Paru',
                'payment_type' => 'bpjs',
                'complaint' => 'Sesak ringan pada malam hari dan batuk hilang timbul, dengan riwayat asma sejak kecil.',
                'diagnosis' => 'Asma Bronkial',
                'consultation_fee' => 150000,
                'procedure_fee' => 50000,
            ],
            [
                'queue_no' => 'A-010',
                'poli' => 'Poli Umum',
                'payment_type' => 'mandiri',
                'complaint' => 'Sakit kepala tegang setelah kurang istirahat, tanpa mual, muntah, atau keluhan neurologis lain.',
                'diagnosis' => 'Tension Headache',
                'consultation_fee' => 150000,
                'procedure_fee' => 50000,
            ],
        ]);

        $this->cleanupLegacySimulationData($branch->uuid, $startDate, $endDate);

        $currentDate = $startDate->copy();
        $rowIndex = 0;
        while ($currentDate->lte($endDate)) {
            $dateSlug = $currentDate->format('Ymd');
            $scenario = $visitScenarios[$rowIndex % $visitScenarios->count()];
            $patient = $patients[$rowIndex % $patients->count()];
            $doctor = $doctors[$rowIndex % $doctors->count()];
            $registeredAt = $currentDate->copy()->setTime(8 + ($rowIndex % 6), 15 + (($rowIndex % 4) * 10))->timestamp;
            $totalAmount = $scenario['consultation_fee'] + $scenario['procedure_fee'];

            $registration = Registration::updateOrCreate(
                [
                    'branch_uuid' => $branch->uuid,
                    'queue_no' => $scenario['queue_no'],
                    'registered_at' => $registeredAt,
                ],
                [
                    'company_uuid' => $companyUuid,
                    'patient_uuid' => $patient->uuid,
                    'doctor_uuid' => $doctor->uuid,
                    'payment_type' => $scenario['payment_type'],
                    'poli' => $scenario['poli'],
                    'notes' => $scenario['complaint'],
                    'status' => 'done',
                    'created_at' => $registeredAt,
                    'updated_at' => $registeredAt,
                ]
            );

            $encounter = Encounter::updateOrCreate(
                ['branch_uuid' => $branch->uuid, 'registration_uuid' => $registration->uuid],
                [
                    'company_uuid' => $companyUuid,
                    'patient_uuid' => $patient->uuid,
                    'doctor_uuid' => $doctor->uuid,
                    'complaint' => $scenario['complaint'],
                    'diagnosis' => $scenario['diagnosis'],
                    'notes' => 'Pasien diedukasi mengenai kepatuhan terapi, pola makan, dan kontrol ulang sesuai jadwal dokter.',
                    'status' => 'posted',
                    'created_at' => $registeredAt,
                    'updated_at' => $registeredAt,
                ]
            );

            $encounterRef = "ENC-SIM-{$dateSlug}";
            $journalDto = JournalEntity::fromArray([
                'branch_uuid' => $branch->uuid,
                'company_uuid' => $companyUuid,
                'reference_no' => $encounterRef,
                'reference_uuid' => $encounter->uuid,
                'journal_date' => $currentDate->timestamp,
                'description' => "Pelayanan poli {$patient->medical_record_no} - {$scenario['diagnosis']}",
                'reference_model' => JournalReferenceModel::ENCOUNTER,
                'reference_type' => JournalReferenceType::ENCOUNTER,
                'lines' => [
                    ['account_uuid' => $account1201->uuid, 'debit' => $totalAmount, 'credit' => 0, 'contact_uuid' => $patient->uuid],
                    ['account_uuid' => $account4101->uuid, 'debit' => 0, 'credit' => $scenario['consultation_fee'], 'contact_uuid' => $patient->uuid],
                    ['account_uuid' => $account4102->uuid, 'debit' => 0, 'credit' => $scenario['procedure_fee'], 'contact_uuid' => $patient->uuid],
                ]
            ]);

            $journalService->upsertJournal($journalDto);

            Receivable::updateOrCreate(
                ['branch_uuid' => $branch->uuid, 'reference_type' => 'encounter', 'reference_uuid' => $encounter->uuid],
                [
                    'amount' => $totalAmount,
                    'paid' => 0,
                    'status' => 'unpaid',
                    'due_date' => $currentDate->copy()->addDays(7)->timestamp,
                    'created_at' => $registeredAt,
                    'updated_at' => $registeredAt,
                ]
            );

            $currentDate->addDay();
            $rowIndex++;
        }

        $this->seedActiveQueueSimulation($branch->uuid, $companyUuid, $patients, $doctors, $today);
    }

    private function cleanupLegacySimulationData(string $branchUuid, Carbon $startDate, Carbon $endDate): void
    {
        $legacyEncounters = Encounter::query()
            ->where('branch_uuid', $branchUuid)
            ->where('complaint', 'Kontrol rutin harian seeder')
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->get();

        if ($legacyEncounters->isEmpty()) {
            return;
        }

        $encounterIds = $legacyEncounters->pluck('uuid');
        $registrationIds = $legacyEncounters->pluck('registration_uuid')->filter()->values();

        DB::transaction(function () use ($branchUuid, $encounterIds, $registrationIds) {
            Journal::query()
                ->where('branch_uuid', $branchUuid)
                ->where('reference_type', JournalReferenceType::ENCOUNTER)
                ->whereIn('reference_uuid', $encounterIds)
                ->delete();

            Receivable::query()
                ->where('branch_uuid', $branchUuid)
                ->where('reference_type', 'encounter')
                ->whereIn('reference_uuid', $encounterIds)
                ->delete();

            Encounter::query()
                ->whereIn('uuid', $encounterIds)
                ->delete();

            Registration::query()
                ->whereIn('uuid', $registrationIds)
                ->delete();
        });
    }

    private function seedActiveQueueSimulation(string $branchUuid, string $companyUuid, $patients, $doctors, Carbon $today): void
    {
        $activeQueue = [
            [
                'queue_no' => 'A-011',
                'status' => 'waiting',
                'poli' => 'Penyakit Dalam',
                'payment_type' => 'bpjs',
                'notes' => 'Kontrol diabetes, hasil gula darah puasa pagi ini 178 mg/dL dan pasien masih sering kesemutan pada kedua kaki.',
                'time' => [8, 0],
            ],
            [
                'queue_no' => 'A-012',
                'status' => 'waiting',
                'poli' => 'Poli Umum',
                'payment_type' => 'mandiri',
                'notes' => 'Batuk pilek sejak 3 hari, demam ringan, dan tenggorokan terasa tidak nyaman saat menelan.',
                'time' => [8, 12],
            ],
            [
                'queue_no' => 'A-013',
                'status' => 'waiting',
                'poli' => 'Poli Umum',
                'payment_type' => 'mandiri',
                'notes' => 'Nyeri ulu hati setelah telat makan, disertai mual dan perut terasa kembung sejak tadi malam.',
                'time' => [8, 25],
            ],
            [
                'queue_no' => 'A-014',
                'status' => 'waiting',
                'poli' => 'Poli Paru',
                'payment_type' => 'bpjs',
                'notes' => 'Sesak napas ringan saat malam dan batuk berulang, riwayat asma sejak usia sekolah.',
                'time' => [8, 40],
            ],
            [
                'queue_no' => 'A-015',
                'status' => 'waiting',
                'poli' => 'Poli Ortopedi',
                'payment_type' => 'mandiri',
                'notes' => 'Nyeri pinggang bawah menjalar ke paha kiri setelah mengangkat barang berat dua hari lalu.',
                'time' => [8, 55],
            ],
            [
                'queue_no' => 'A-016',
                'status' => 'in_progress',
                'poli' => 'Penyakit Dalam',
                'payment_type' => 'bpjs',
                'notes' => 'Kontrol hipertensi, pasien mengeluh pusing berputar dan tekanan darah rumah masih di atas target.',
                'time' => [9, 10],
            ],
            [
                'queue_no' => 'A-017',
                'status' => 'in_progress',
                'poli' => 'Poli Kulit',
                'payment_type' => 'mandiri',
                'notes' => 'Ruam kemerahan dan gatal pada lipatan siku sejak satu minggu, terutama setelah berkeringat.',
                'time' => [9, 25],
            ],
        ];

        foreach ($activeQueue as $index => $row) {
            [$hour, $minute] = $row['time'];
            $registeredAt = $today->copy()->setTime($hour, $minute);
            $patient = $patients[($index + 2) % $patients->count()];
            $doctor = $doctors[$index % $doctors->count()];

            Registration::query()->updateOrCreate(
                [
                    'branch_uuid' => $branchUuid,
                    'queue_no' => $row['queue_no'],
                ],
                [
                    'company_uuid' => $companyUuid,
                    'patient_uuid' => $patient->uuid,
                    'doctor_uuid' => $doctor->uuid,
                    'payment_type' => $row['payment_type'],
                    'poli' => $row['poli'],
                    'notes' => $row['notes'],
                    'status' => $row['status'],
                    'registered_at' => $registeredAt->timestamp,
                ]
            );
        }
    }
}
