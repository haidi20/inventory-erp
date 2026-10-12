<?php

namespace Database\Factories\Operational;

use App\Modules\Operational\Sales\Models\SalesOrder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SalesOrderFactory extends Factory
{
    protected $model = SalesOrder::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => (string) Str::uuid(),
            'company_uuid' => (string) Str::uuid(),
            'person_uuid' => (string) Str::uuid(),
            'location_uuid' => (string) Str::uuid(),
            'payment_term_uuid' => (string) Str::uuid(),
            'sales_order_no' => 'SO-' . $this->faker->unique()->numerify('####'),
            'sales_order_date' => time(),
            'status' => 'Open',
            'description' => $this->faker->sentence(),
        ];
    }
}
