<?php

namespace App\Modules\Finance;

use Illuminate\Support\ServiceProvider;
use App\Modules\Finance\Journal\Repositories\JournalRepositoryInterface;
use App\Modules\Finance\Journal\Repositories\JournalRepository;
use App\Modules\Finance\Journal\Repositories\AccountingRepositoryInterface;
use App\Modules\Finance\Journal\Repositories\AccountingRepository;
use App\Modules\Finance\Journal\Repositories\InitialBalanceRepositoryInterface;
use App\Modules\Finance\Journal\Repositories\InitialBalanceRepository;
use App\Modules\Finance\Journal\Repositories\TransactionDetailRepositoryInterface;
use App\Modules\Finance\Journal\Repositories\TransactionDetailRepository;
use App\Modules\Finance\Journal\Repositories\TransactionSequenceRepository;
use App\Modules\Finance\Journal\Repositories\RecurringJournalRepository;
use App\Modules\Finance\Ledger\Repositories\GeneralLedgerRepositoryInterface;
use App\Modules\Finance\Ledger\Repositories\GeneralLedgerRepository;
use App\Modules\Finance\Ledger\Repositories\OpeningBalanceRepositoryInterface;
use App\Modules\Finance\Ledger\Repositories\OpeningBalanceRepository;

class FinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(JournalRepositoryInterface::class, JournalRepository::class);
        $this->app->bind(AccountingRepositoryInterface::class, AccountingRepository::class);
        $this->app->bind(InitialBalanceRepositoryInterface::class, InitialBalanceRepository::class);
        $this->app->bind(TransactionDetailRepositoryInterface::class, TransactionDetailRepository::class);
        $this->app->bind(GeneralLedgerRepositoryInterface::class, GeneralLedgerRepository::class);
        $this->app->bind(OpeningBalanceRepositoryInterface::class, OpeningBalanceRepository::class);
        $this->app->bind(TransactionSequenceRepository::class, TransactionSequenceRepository::class);
        $this->app->bind(RecurringJournalRepository::class, RecurringJournalRepository::class);
    }

    public function boot(): void {}
}