<?php

namespace Database\Factories\Master;

use App\Modules\Operational\Master\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => (string) Str::uuid(),
            'company_uuid' => (string) Str::uuid(),
            'code' => 'TREAT-' . $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->words(3, true),
            'duration_minutes' => $this->faker->randomElement([15, 30, 60, 90]),
            'price' => $this->faker->randomFloat(2, 50000, 500000),
            'doctor_fee' => $this->faker->randomFloat(2, 10000, 100000),
            'is_active' => true,
        ];
    }
}
