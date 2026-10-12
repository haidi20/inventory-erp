<?php

namespace App\Modules\Finance\Journal\Repositories;


use Illuminate\Support\Facades\DB;
use App\Modules\Finance\Journal\Models\RecurringJournal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * Repository untuk Jurnal Berulang (Recurring).
 */
class RecurringJournalRepository
{
    /**
     * Mendapatkan template jurnal recurring yang aktif dan harus dijalankan hari ini.
     */
    public function getActiveToRun(): Collection
    {
        return RecurringJournal::where('status', 'active')
            ->where('next_run_date', '<=', Carbon::now()->endOfDay()->timestamp)
            ->get();
    }

    /**
     * Update data template recurring.
     */
    public function update(string $uuid, array $data): bool
    {
        DB::beginTransaction();
        try {
            DB::commit();
            return RecurringJournal::where('uuid', $uuid)->update($data) > 0;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
