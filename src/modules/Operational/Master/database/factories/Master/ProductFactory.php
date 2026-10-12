<?php

namespace Database\Factories\Master;

use App\Modules\Operational\Master\Models\Product;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => Branch::factory(),
            'company_uuid' => Company::factory(),
            'code' => $this->faker->unique()->bothify('OBT-####'),
            'name' => $this->faker->words(3, true),
            'product_uuid' => null,
            'base_unit_uuid' => null,
            'min_stock' => 10,
            'max_stock' => 100,
            'base_price_buy' => $this->faker->numberBetween(5000, 50000),
            'base_price_sell' => $this->faker->numberBetween(6000, 70000),
            'is_active' => true,
        ];
    }
}
