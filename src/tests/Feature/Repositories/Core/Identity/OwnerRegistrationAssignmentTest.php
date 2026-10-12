<?php

namespace Tests\Feature\Repositories\Core\Identity;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Identity\Repositories\OwnerRegistrationRepository;
use App\Modules\Core\Identity\Repositories\OwnerRegistrationRepositoryInterface;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regresi TEST-001: assignDefaultCompany()/assignDefaultBranch() sebelumnya
 * memakai create() sehingga pemanggilan ulang memicu duplicate-entry
 * (500 pada register). Setelah fix firstOrNew+save, pemanggilan berulang
 * harus idempotent: satu baris, is_default tetap true.
 */
class OwnerRegistrationAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private OwnerRegistrationRepositoryInterface $repository;

    private User $user;

    private Company $company;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(OwnerRegistrationRepository::class);

        $this->company = Company::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Klinik Idempoten',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'uuid' => (string) Str::uuid(),
            'short_name' => 'IDP',
            'name' => 'Cabang Idempoten',
            'is_active' => true,
            'company_uuid' => $this->company->uuid,
        ]);

        $this->user = User::factory()->create([
            'email' => 'idempoten@klinik.local',
            'password' => Hash::make('secret123'),
        ]);
    }

    public function test_assign_default_company_is_idempotent()
    {
        $this->repository->assignDefaultCompany($this->user->uuid, $this->company->uuid);
        $this->repository->assignDefaultCompany($this->user->uuid, $this->company->uuid);

        // is_default ada di kolom pivot, bukan atribut model Company;
        // query langsung model pivot UserCompany.
        $rows = UserCompany::query()
            ->where('user_uuid', $this->user->uuid)
            ->get();

        $this->assertCount(1, $rows, 'Pemanggilan ulang tidak boleh membuat baris duplikat di user_companies.');
        $this->assertTrue((bool) $rows->first()->is_default);
    }

    public function test_assign_default_branch_is_idempotent()
    {
        $this->repository->assignDefaultBranch($this->user->uuid, $this->company->uuid, $this->branch->uuid);
        $this->repository->assignDefaultBranch($this->user->uuid, $this->company->uuid, $this->branch->uuid);

        $rows = UserBranch::query()
            ->where('user_uuid', $this->user->uuid)
            ->where('company_uuid', $this->company->uuid)
            ->get();

        $this->assertCount(1, $rows, 'Pemanggilan ulang tidak boleh membuat baris duplikat di user_branches.');
        $this->assertTrue((bool) $rows->first()->is_default);
    }
}
