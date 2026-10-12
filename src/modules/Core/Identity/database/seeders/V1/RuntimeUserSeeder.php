<?php

namespace Database\Seeders\V1;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RuntimeUserSeeder extends Seeder
{
    public function run(): void
    {
        $sandboxBranch = Branch::query()->where('short_name', 'KSB')->first();

        if (!$sandboxBranch) {
            return;
        }

        foreach ((array) config('libraries.users', []) as $email => $data) {
            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => (string) ($data['name'] ?? $email),
                    'password' => Hash::make('klinik123'),
                    // Kolom legacy branch_uuid/company_uuid sudah di-drop dari tabel users;
                    // konteks multi-org diset lewat pivot di bawah.
                ],
            );

            UserCompany::query()->updateOrCreate(
                ['user_uuid' => $user->uuid, 'company_uuid' => $sandboxBranch->company_uuid],
                ['is_default' => true],
            );

            UserBranch::query()->updateOrCreate(
                ['user_uuid' => $user->uuid, 'company_uuid' => $sandboxBranch->company_uuid, 'branch_uuid' => $sandboxBranch->uuid],
                ['is_default' => true],
            );

            if (!empty($data['role'])) {
                $role = Role::query()->where('name', (string) $data['role'])->first();
                if ($role) {
                    $user->syncRoles([$role->name]);
                }
            }
        }
    }
}
