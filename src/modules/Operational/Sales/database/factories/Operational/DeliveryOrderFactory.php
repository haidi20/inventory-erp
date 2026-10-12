<?php

namespace Database\Factories\Operational;

use App\Modules\Operational\Sales\Models\DeliveryOrder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DeliveryOrderFactory extends Factory
{
    protected $model = DeliveryOrder::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => (string) Str::uuid(),
            'company_uuid' => (string) Str::uuid(),
            'sales_order_uuid' => null,
            'person_uuid' => (string) Str::uuid(),
            'location_uuid' => (string) Str::uuid(),
            'delivery_no' => 'DO-' . $this->faker->unique()->numerify('####'),
            'delivery_date' => time(),
            'description' => $this->faker->sentence(),
        ];
    }
}
