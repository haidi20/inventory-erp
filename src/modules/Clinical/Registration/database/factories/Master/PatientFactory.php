<?php

namespace Database\Factories\Master;

use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => Branch::factory(),
            'company_uuid' => (string) Str::uuid(),
            'medical_record_no' => $this->faker->unique()->bothify('RM-######'),
            'name' => $this->faker->name(),
            'birth_date' => $this->faker->dateTimeBetween('-60 years', '-1 year')->getTimestamp(),
            'gender' => $this->faker->randomElement(['L', 'P']),
            'phone' => $this->faker->phoneNumber(),
            'is_active' => true,
        ];
    }
}
