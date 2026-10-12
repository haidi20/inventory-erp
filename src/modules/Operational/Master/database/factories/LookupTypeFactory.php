<?php

namespace Database\Factories;

use App\Modules\Operational\Master\Models\LookupType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LookupTypeFactory extends Factory
{
    protected $model = LookupType::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'code' => strtoupper($this->faker->unique()->lexify('????')),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'company_uuid' => (string) Str::uuid(),
            'is_active' => true,
        ];
    }
}
