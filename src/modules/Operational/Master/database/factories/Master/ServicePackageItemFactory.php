<?php

namespace Database\Factories\Master;

use App\Modules\Operational\Master\Models\ServicePackageItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServicePackageItemFactory extends Factory
{
    protected $model = ServicePackageItem::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => (string) Str::uuid(),
            'company_uuid' => (string) Str::uuid(),
            'service_package_uuid' => (string) Str::uuid(),
            'item_type' => $this->faker->randomElement(['service', 'product']),
            'item_uuid' => (string) Str::uuid(),
            'qty' => $this->faker->numberBetween(1, 10),
        ];
    }
}
