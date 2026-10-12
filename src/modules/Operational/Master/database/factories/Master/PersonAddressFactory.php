<?php

namespace Database\Factories\Master;

use App\Modules\Operational\Master\Models\PersonAddress;
use App\Modules\Operational\Master\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PersonAddressFactory extends Factory
{
    protected $model = PersonAddress::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'person_uuid' => Person::factory(),
            'type' => $this->faker->randomElement(['Home', 'Office', 'Billing', 'Shipping']),
            'address1' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'province' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'country' => 'Indonesia',
            'is_primary' => false,
            'deleted_at' => 0,
        ];
    }
}
