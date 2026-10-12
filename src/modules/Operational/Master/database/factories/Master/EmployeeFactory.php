<?php

namespace Database\Factories\Master;

use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => Branch::factory(),
            'company_uuid' => (string) Str::uuid(),
            'nik' => $this->faker->numerify('################'),
            'name' => $this->faker->name(),
            'role' => $this->faker->randomElement(['dokter', 'perawat', 'admin']),
            'specialization' => $this->faker->word(),
            'is_active' => true,
        ];
    }
}
