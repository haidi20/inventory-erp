<?php

namespace App\Modules\Core\Identity\Imports;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Authorization\Models\Role;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserImport implements ToCollection, WithHeadingRow
{
    public function __construct(
        private readonly string $actorUuid,
        private readonly string $branchUuid,
        private readonly string $companyUuid,
    )
    {
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $email = strtolower(trim($row['email'] ?? ''));
            if (empty($email)) continue;

            $existingUser = User::query()
                ->where('email', $email)
                ->where('company_uuid', $this->companyUuid)
                ->first();
            $password = trim((string) ($row['password'] ?? ''));

            if ($existingUser === null && $password === '') {
                throw ValidationException::withMessages([
                    'file' => "Password wajib diisi untuk user baru dengan email {$email}.",
                ]);
            }

            $payload = [
                'name' => trim($row['nama'] ?? 'User Import'),
                'branch_uuid' => $this->branchUuid,
                'company_uuid' => $this->companyUuid,
            ];

            if ($password !== '') {
                $payload['password'] = Hash::make($password);
            }

            $user = User::updateOrCreate(
                [
                    'email' => $email,
                    'company_uuid' => $this->companyUuid,
                ],
                $payload
            );

            UserCompany::query()->updateOrCreate(
                ['user_uuid' => $user->uuid, 'company_uuid' => $this->companyUuid],
                ['is_default' => true],
            );

            if (!empty($this->branchUuid)) {
                UserBranch::query()->updateOrCreate(
                    ['user_uuid' => $user->uuid, 'company_uuid' => $this->companyUuid, 'branch_uuid' => $this->branchUuid],
                    ['is_default' => true],
                );
            }

            // Handle Role if provided
            $roleLabel = trim($row['role'] ?? '');
            if (!empty($roleLabel)) {
                $role = Role::where('label', $roleLabel)->orWhere('name', strtolower($roleLabel))->first();
                if ($role) {
                    $user->syncRoles([$role->uuid]);
                }
            }
        }
    }
}
