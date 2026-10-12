<?php

namespace Database\Factories;

use App\Modules\Operational\Master\Models\LookupValue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LookupValueFactory extends Factory
{
    protected $model = LookupValue::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'lookup_set_uuid' => (string) Str::uuid(),
            'code' => strtoupper($this->faker->unique()->lexify('????')),
            'display_name' => $this->faker->word(),
            'value' => $this->faker->word(),
            'program_code' => strtoupper($this->faker->lexify('????')),
            'description' => $this->faker->sentence(),
            'seq' => $this->faker->numberBetween(1, 100),
            'order_priority' => $this->faker->numberBetween(1, 100),
            'is_default' => false,
            'is_active' => true,
            'active_flag' => 'Y',
            'price_up_beli' => '0.00000',
            'diskon_pilih' => '0.00000',
            'persen1' => '0.00000',
            'persen2' => '0.00000',
            'company_uuid' => (string) Str::uuid(),
        ];
    }
}
