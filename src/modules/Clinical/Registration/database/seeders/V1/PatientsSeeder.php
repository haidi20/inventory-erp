<?php

namespace Database\Seeders\V1;

use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Core\Organization\Models\Branch;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PatientsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RuntimeCompanySeeder::class);

        $faker = Faker::create('id_ID');

        $existing2026 = Patient::where('medical_record_no', 'like', 'RM-2026%')
            ->where('medical_record_no', 'like', 'RM-2026%')
            ->count();

        $baseNik = 2026000000000000 + $existing2026;
        $branch = Branch::query()
            ->whereIn('short_name', ['KDM', RuntimeCompanySeeder::SANDBOX_BRANCH_SHORT_NAME])
            ->orderByRaw("CASE WHEN short_name = 'KDM' THEN 0 ELSE 1 END")
            ->firstOrFail();
        $rows = [];

        for ($i = 1; $i <= 100; $i++) {
            $seq = $existing2026 + $i;
            $medicalRecordNo = 'RM-2026'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

            $createdAt = Carbon::create(
                2026,
                rand(1, 12),
                rand(1, 28),
                rand(0, 23),
                rand(0, 59),
                rand(0, 59)
            );

            $rows[] = [
                'uuid' => (string) Str::uuid(),
                'branch_uuid' => $branch->uuid,
                'company_uuid' => $branch->company_uuid,
                'medical_record_no' => $medicalRecordNo,
                'name' => $faker->name(),
                'birth_date' => Carbon::parse($faker->date())->timestamp,
                'gender' => $faker->randomElement(['M', 'F']),
                'phone' => '08'.str_pad((string) rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                'email' => 'pasien'.$seq.'@example.com',
                'address' => $faker->address(),
                'bpjs_number' => str_pad((string) rand(0, 9999999999999), 13, '0', STR_PAD_LEFT),
                'nik' => str_pad((string) ($baseNik + $i), 16, '0', STR_PAD_LEFT),
                'is_active' => true,
                'created_at' => $createdAt->timestamp,
                'updated_at' => $createdAt->timestamp,
            ];
        }

        Patient::insert($rows);
    }
}
