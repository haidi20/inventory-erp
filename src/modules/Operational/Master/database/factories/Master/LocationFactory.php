<?php

namespace Database\Factories\Master;

use App\Modules\Operational\Master\Models\Location;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'uuid'         => (string) Str::uuid(),
            'branch_uuid'  => Branch::factory(),
            'company_uuid' => Company::factory(),
            'type'         => 'location',
            'code'         => $this->faker->unique()->lexify('WH-???'),
            'name'         => $this->faker->company() . ' Location',
            'description'  => $this->faker->sentence(),
            'is_active'    => true,
            'can_sell'     => true,
            'is_primary'   => false,
            'address_uuid' => null,
            'created_at'   => time(),
            'updated_at'   => time(),
            'deleted_at'   => 0,
        ];
    }
}