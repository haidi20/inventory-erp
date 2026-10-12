<?php

namespace Database\Factories;

use App\Modules\Operational\Master\Models\PriceLevel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PriceLevelFactory extends Factory
{
    protected $model = PriceLevel::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => $this->faker->word(),
            'is_active' => true,
        ];
    }
}
