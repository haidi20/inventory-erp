<?php

namespace Tests\Feature\Accounting;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpeningBalanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Skenario #17: Validasi Saldo Awal (Opening Balance)
     * Memastikan Closing Balance periode sebelumnya menjadi Opening Balance periode baru.
     */
    public function test_opening_balance_initialization_from_previous_period()
    {
        $branch = Branch::create(['name' => 'Klinik Demo', 'short_name' => 'KDM']);

        // 1. Buat Periode Januari (Closed)
        $periodJan = ClosingPeriod::create([
            'branch_uuid' => $branch->uuid,
            'period'    => '2026-01',
            'name'      => 'Januari 2026',
            'start_date'=> '2026-01-01',
            'end_date'  => '2026-01-31',
            'status'    => 'closed',
            'closed_at' => '2026-01-31',
        ]);

        // 2. Buat Akun Neraca (Asset) & Saldo Akhir Jan
        $accountAsset = Account::create(['code' => '1101', 'name' => 'Kas', 'type' => 'asset', 'type_saldo' => 'debit']);
        AccountBalance::create([
            'branch_uuid' => $branch->uuid,
            'account_uuid'=> $accountAsset->uuid,
            'period'    => '2026-01',
            'opening_balance' => 0,
            'total_debit' => 100000,
            'total_credit' => 40000,
            'closing_balance' => 60000, // 100k - 40k
        ]);

        // 3. Buat Akun Laba Rugi (Revenue) & Saldo Akhir Jan
        $accountRev = Account::create(['code' => '4101', 'name' => 'Pendapatan', 'type' => 'revenue', 'type_saldo' => 'credit']);
        AccountBalance::create([
            'branch_uuid' => $branch->uuid,
            'account_uuid'=> $accountRev->uuid,
            'period'    => '2026-01',
            'opening_balance' => 0,
            'total_debit' => 0,
            'total_credit' => 50000,
            'closing_balance' => 50000,
        ]);

        // 4. Buat Periode Februari (Open)
        // Pembuatan periode baru otomatis meng-carryover Saldo Akhir periode sebelumnya via boot listener
        $periodFeb = ClosingPeriod::create([
            'branch_uuid' => $branch->uuid,
            'period'    => '2026-02',
            'name'      => 'Februari 2026',
            'start_date'=> '2026-02-01',
            'end_date'  => '2026-02-28',
            'status'    => 'open',
        ]);

        // 5. Verifikasi Saldo Awal di Februari:

        // - Akun Neraca (Asset) HARUS membawa 60000
        $balanceAssetFeb = AccountBalance::where('account_uuid', $accountAsset->uuid)
            ->where('period', '2026-02')
            ->first();

        $this->assertNotNull($balanceAssetFeb);
        $this->assertEquals(60000, (float)$balanceAssetFeb->opening_balance);

        // - Akun Laba Rugi (Revenue) HARUS MULAI dari 0!
        $balanceRevFeb = AccountBalance::where('account_uuid', $accountRev->uuid)
            ->where('period', '2026-02')
            ->first();

        $this->assertNotNull($balanceRevFeb);
        $this->assertEquals(0, (float)$balanceRevFeb->opening_balance, "Akun Laba Rugi harus reset ke 0 di periode baru.");
    }
}
