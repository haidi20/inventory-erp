<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupE2eData extends Command
{
    protected $signature = 'e2e:cleanup';

    protected $description = 'Hapus data yang dibuat oleh E2E test login dan register';

    public function handle(): int
    {
        $companies = DB::table('companies')
            ->where('name', 'like', 'Klinik E2E %')
            ->pluck('uuid');
        $users = DB::table('users')
            ->where('email', 'like', '%@e2e.test')
            ->pluck('uuid');

        if ($users->isNotEmpty()) {
            DB::table('user_branches')->whereIn('user_uuid', $users)->delete();
            DB::table('user_companies')->whereIn('user_uuid', $users)->delete();
            DB::table('model_has_roles')->whereIn('model_uuid', $users)->delete();
            DB::table('model_has_permissions')->whereIn('model_uuid', $users)->delete();
            DB::table('users')->whereIn('uuid', $users)->delete();
        }

        if ($companies->isNotEmpty()) {
            DB::table('user_branches')->whereIn('company_uuid', $companies)->delete();
            DB::table('branches')->whereIn('company_uuid', $companies)->delete();
            DB::table('companies')->whereIn('uuid', $companies)->delete();
        }

        $productCodes = ['AAA-E2E-FILTER-%', 'ITM-E2E-%', 'E2E-FRM-%'];
        $items = DB::table('items')->where(function ($query) use ($productCodes) {
            foreach ($productCodes as $code) {
                $query->orWhere('code', 'like', $code);
            }
        })->pluck('uuid');

        if ($items->isNotEmpty()) {
            DB::table('item_stocks')->whereIn('item_uuid', $items)->delete();
            DB::table('item_opening_balances')->whereIn('item_uuid', $items)->delete();
            DB::table('items')->whereIn('uuid', $items)->delete();
        }

        $units = DB::table('units')
            ->where('code', 'like', 'E2E-UNIT-%')
            ->pluck('uuid');

        if ($units->isNotEmpty()) {
            DB::table('units')->whereIn('uuid', $units)->delete();
        }

        $lookupValues = DB::table('lookup_values')
            ->where('display_name', 'like', 'E2E Filter %')
            ->pluck('uuid');

        if ($lookupValues->isNotEmpty()) {
            DB::table('lookup_value_details')->whereIn('lookup_value_uuid', $lookupValues)->delete();
            DB::table('lookup_values')->whereIn('uuid', $lookupValues)->delete();
        }

        DB::table('lookup_sets')
            ->where('name', 'like', 'E2E Product Filter %')
            ->delete();

        DB::table('locations')->where('code', 'E2E-FRM-WH')->delete();
        DB::table('price_levels')->where('code', 'E2E-FRM-PL')->delete();

        // Kartu kanban buatan E2E (kanban.board.view.spec.ts) — pengaman jika
        // run crash sebelum afterAll sempat berjalan.
        $kanbanCards = DB::table('kanban_cards')
            ->where('title', 'like', 'E2E-SORA-KANBAN%')
            ->delete();

        $this->info("E2E cleanup selesai: {$users->count()} user, {$companies->count()} perusahaan, {$items->count()} item, {$kanbanCards} kartu kanban.");

        return self::SUCCESS;
    }
}
