<?php

namespace Tests\Feature\Accounting;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClosingPeriodTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Skenario #22: Validasi Periode Berurutan (Gap)
     * Mencegah pembuatan periode akuntansi lompat bulan.
     */
    public function test_cannot_create_gap_between_periods()
    {
        $branch = Branch::create(['name' => 'Klinik Demo', 'short_name' => 'KDM']);

        // 1. Buat Periode Januari
        ClosingPeriod::create([
            'branch_uuid' => $branch->uuid,
            'period'    => '2026-01',
            'name'      => 'Januari 2026',
            'start_date'=> '2026-01-01',
            'end_date'  => '2026-01-31',
            'status'    => 'open',
        ]);

        // 2. Ekspektasi Exception saat buat Maret (Loncat Februari)
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cannot create closing period. There is a gap from the previous period.");

        // 3. Action
        ClosingPeriod::create([
            'branch_uuid' => $branch->uuid,
            'period'    => '2026-03',
            'name'      => 'Maret 2026',
            'start_date'=> '2026-03-01',
            'end_date'  => '2026-03-31',
            'status'    => 'open',
        ]);
    }

    /**
     * Skenario #22: Validasi Periode Berurutan (Overlap)
     * Mencegah pembuatan periode overlap dengan tanggal existing.
     */
    public function test_cannot_overlap_periods()
    {
        $branch = Branch::create(['name' => 'Klinik Demo', 'short_name' => 'KDM']);

        // 1. Buat Periode Januari
        ClosingPeriod::create([
            'branch_uuid' => $branch->uuid,
            'period'    => '2026-01',
            'name'      => 'Januari 2026',
            'start_date'=> '2026-01-01',
            'end_date'  => '2026-01-31',
            'status'    => 'open',
        ]);

        // 2. Ekspektasi Exception saat buat Overlap
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Closing period dates overlap with an existing period.");

        // 3. Action (Overlap 15 Jan - 15 Feb)
        ClosingPeriod::create([
            'branch_uuid' => $branch->uuid,
            'period'    => '2026-02',
            'name'      => 'Februari 2026',
            'start_date'=> '2026-01-15', // Overlap!
            'end_date'  => '2026-02-15',
            'status'    => 'open',
        ]);
    }
}
