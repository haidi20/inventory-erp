<?php

namespace Database\Factories\Master;

use App\Modules\Operational\Master\Models\Person;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => Branch::factory(),
            'company_uuid' => Company::factory(),
            'code' => 'P-' . $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->name(),
            'type' => $this->faker->randomElement(['customer', 'supplier', 'personnel']),
            'is_active' => true,
            'deleted_at' => 0
        ];
    }
}
