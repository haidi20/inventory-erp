<?php

namespace Tests\Feature\Infrastructure;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Finance\Journal\Models\TransactionSequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * TDD Section 30.13 -- TransactionSequence (penomoran dokumen) wajib
 * ter-scope multi-org: dua cabang/company berbeda tidak boleh saling
 * melihat sequence, dan pembuatan harus mengisi kolom scope dari
 * konteks user login.
 *
 * Skema: transaction_sequences punya branch_uuid (NOT NULL) +
 * company_uuid (nullable) + unique (branch_uuid, type_code, period).
 */
class TransactionSequenceScopeTest extends TestCase
{
    use RefreshDatabase;

    private Company $companyA;
    private Company $companyB;
    private Branch $branchA;
    private Branch $branchB;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyA = Company::factory()->create(['deleted_at' => 0]);
        $this->companyB = Company::factory()->create(['deleted_at' => 0]);
        $this->branchA = Branch::factory()->create(['company_uuid' => $this->companyA->uuid]);
        $this->branchB = Branch::factory()->create(['company_uuid' => $this->companyB->uuid]);

        $this->user = User::factory()->create();

        UserCompany::query()->create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->companyA->uuid,
            'is_default' => true,
        ]);
        UserBranch::query()->create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->companyA->uuid,
            'branch_uuid' => $this->branchA->uuid,
            'is_default' => true,
        ]);
    }

    private function actingAsScopedUser(): void
    {
        $this->actingAs($this->user);
        Auth::shouldUse('web');
    }

    private function attributesFor(string $companyUuid, string $branchUuid): array
    {
        return [
            'company_uuid' => $companyUuid,
            'branch_uuid'  => $branchUuid,
            'type_code'    => 'INV',
            'period'       => '202610',
            'last_index'   => 5,
        ];
    }

    public function test_sequence_is_scoped_to_logged_in_user_company(): void
    {
        $this->actingAsScopedUser();

        $seqA = TransactionSequence::query()->create(
            $this->attributesFor($this->companyA->uuid, $this->branchA->uuid)
        );
        TransactionSequence::query()->create(
            $this->attributesFor($this->companyB->uuid, $this->branchB->uuid)
        );

        // Global scope otomatis: hanya sequence company A yang terlihat
        $this->assertSame(1, TransactionSequence::query()->count());
        $this->assertSame($seqA->getKey(), TransactionSequence::query()->first()->getKey());
    }

    public function test_create_fills_scope_columns_from_context(): void
    {
        $this->actingAsScopedUser();

        // company_uuid/branch_uuid TIDAK diisi eksplisit -- trait wajib
        // mengisinya dari konteks user login.
        $seq = TransactionSequence::query()->create([
            'type_code'  => 'JV',
            'period'     => '202610',
            'last_index' => 1,
        ]);

        $this->assertSame($this->companyA->uuid, $seq->company_uuid);
        $this->assertSame($this->branchA->uuid, $seq->branch_uuid);
    }

    public function test_sequence_query_filter_by_branch_when_trait_present(): void
    {
        $this->actingAsScopedUser();

        TransactionSequence::query()->create(
            $this->attributesFor($this->companyA->uuid, $this->branchA->uuid)
        );

        // User dengan scope branch A: query tanpa where manual tetap
        // terfilter; verifikasi model menerapkan kedua trait.
        $usedTraits = class_uses_recursive(TransactionSequence::class);
        $this->assertContains(\App\Shared\Traits\HasCompanyScope::class, $usedTraits);
        $this->assertContains(\App\Shared\Traits\HasBranchScope::class, $usedTraits);
    }
}
