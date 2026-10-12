<?php

namespace Database\Factories\Master;

use App\Modules\Operational\Master\Models\PersonCustomField;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PersonCustomFieldFactory extends Factory
{
    protected $model = PersonCustomField::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'person_uuid' => (string) Str::uuid(),
            'field_key' => $this->faker->word(),
            'field_value' => $this->faker->sentence(),
            'deleted_at' => 0,
        ];
    }
}