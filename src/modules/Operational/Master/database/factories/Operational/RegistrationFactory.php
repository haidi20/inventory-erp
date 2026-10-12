<?php

namespace Database\Factories\Operational;

use App\Modules\Clinical\Registration\Models\Registration;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => Branch::factory(),
            'company_uuid' => (string) Str::uuid(),
            'patient_uuid' => Patient::factory(),
            'doctor_uuid' => Employee::factory(),
            'queue_no' => $this->faker->bothify('A-##'),
            'poli' => $this->faker->randomElement(['Umum', 'Gigi', 'KIA']),
            'payment_type' => $this->faker->randomElement(['mandiri', 'bpjs']),
            'status' => 'waiting',
            'registered_at' => time(),
        ];
    }
}
