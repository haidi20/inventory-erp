<?php

namespace Database\Factories;

use App\Modules\Operational\Master\Models\LookupValueDetail;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LookupValueDetailFactory extends Factory
{
    protected $model = LookupValueDetail::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'lookup_value_uuid' => (string) Str::uuid(),
            'idx' => $this->faker->numberBetween(1, 10),
            'value' => $this->faker->word(),
        ];
    }
}
