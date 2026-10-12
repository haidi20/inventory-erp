<?php

namespace Database\Factories;

use App\Modules\Operational\Master\Models\ProductPrice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductPriceFactory extends Factory
{
    protected $model = ProductPrice::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => (string) Str::uuid(),
            'company_uuid' => (string) Str::uuid(),
            'item_uuid' => (string) Str::uuid(),
            'unit_uuid' => (string) Str::uuid(),
            'price_level_uuid' => (string) Str::uuid(),
            'price' => $this->faker->numberBetween(1000, 1000000),
            'is_active' => true,
        ];
    }
}
