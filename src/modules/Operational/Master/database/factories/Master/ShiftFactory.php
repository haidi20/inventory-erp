<?php

namespace Database\Factories\Master;

use App\Modules\Operational\Master\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    public function definition(): array
    {
        return [
            'shift_number' => $this->faker->numberBetween(1, 3),
            'date' => time(),
            'start_time' => time(),
            'opening_balance' => 0,
            'branch_uuid' => (string) Str::uuid(),
            'company_uuid' => (string) Str::uuid(),
            'location_uuid' => (string) Str::uuid(),
            'user_uuid' => (string) Str::uuid(),
            'created_at' => time(),
            'updated_at' => time(),
        ];
    }
}
