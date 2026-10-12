<?php

namespace Database\Factories\Master;

use App\Modules\Operational\Master\Models\Unit;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => Branch::factory(),
            'company_uuid' => Company::factory(),
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'created_at' => time(),
            'updated_at' => time(),
        ];
    }
}
