<?php

namespace Database\Factories\Operational;

use App\Modules\Operational\Sales\Models\DeliveryOrderDetail;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DeliveryOrderDetailFactory extends Factory
{
    protected $model = DeliveryOrderDetail::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'delivery_order_uuid' => (string) Str::uuid(),
            'branch_uuid' => (string) Str::uuid(),
            'company_uuid' => (string) Str::uuid(),
            'item_uuid' => (string) Str::uuid(),
            'qty' => $this->faker->numberBetween(1, 20),
            'description' => $this->faker->sentence(),
        ];
    }
}
