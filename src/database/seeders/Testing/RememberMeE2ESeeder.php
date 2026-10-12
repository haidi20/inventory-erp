<?php

namespace Database\Seeders\Testing;

use Database\Seeders\V1\RolePermissionSeeder;
use Database\Seeders\V1\RuntimeCompanySeeder;
use Database\Seeders\V1\SuperadminSeeder;
use Illuminate\Database\Seeder;

class RememberMeE2ESeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RuntimeCompanySeeder::class,
            RolePermissionSeeder::class,
            SuperadminSeeder::class,
        ]);
    }
}
