<?php

namespace Database\Factories\Master;

use App\Modules\Operational\Master\Models\ServicePackage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServicePackageFactory extends Factory
{
    protected $model = ServicePackage::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => (string) Str::uuid(),
            'company_uuid' => (string) Str::uuid(),
            'code' => 'PKG-' . $this->faker->unique()->numberBetween(100, 999),
            'name' => $this->faker->words(3, true) . ' Package',
            'price' => $this->faker->randomFloat(2, 200000, 2000000),
            'is_active' => true,
        ];
    }
}
