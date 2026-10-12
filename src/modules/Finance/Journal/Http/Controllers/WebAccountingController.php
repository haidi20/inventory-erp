<?php

namespace App\Modules\Finance\Journal\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Finance\Journal\Services\AccountingCycleService;
use App\Modules\Finance\Journal\Services\AccountService;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WebAccountingController extends Controller
{
    public function __construct(
        private AccountingCycleService $accountingCycleService,
        private AccountService $accountService,
        private JournalService $journalService,
    ) {}

    private function buildFilters(Request $request, array $extra = []): array
    {
        return array_merge([
            'search' => (string) $request->string('search')->trim(),
            'page' => $request->integer('page', 1),
            'per_page' => $request->integer('per_page', 10),
        ], $extra);
    }

    public function showIndex(): RedirectResponse
    {
        if (! auth()->user()->can('akuntansi.view')) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        return redirect('/inventory?tab=akuntansi');
    }

    public function showPeriods(Request $request): Response
    {
        return Inertia::render('Finance/Journal/presentations/views/AccountingPeriodsIndex', [
            'filters' => $this->buildFilters($request),
        ]);
    }

    public function storePeriod(): RedirectResponse
    {
        $month = str_pad(request('month'), 2, '0', STR_PAD_LEFT);
        $year = request('year');
        $period = "{$year}-{$month}";
        $this->accountingCycleService->createPeriod($period);

        return redirect('/accounting/periods')->with('success', "Periode {$period} berhasil ditambahkan.");
    }

    public function activatePeriod(string $uuid): RedirectResponse
    {
        $this->accountingCycleService->activatePeriod($uuid);

        return redirect('/accounting/periods')->with('success', 'Periode berhasil diaktifkan.');
    }

    public function destroyPeriod(string $uuid): RedirectResponse
    {
        $this->accountingCycleService->deletePeriod($uuid);

        return redirect('/accounting/periods')->with('success', 'Periode berhasil dihapus.');
    }

    public function showParentAccounts(Request $request): Response
    {
        return Inertia::render('Finance/Journal/presentations/views/ParentAccountsIndex', [
            'filters' => $this->buildFilters($request, ['type' => $request->string('type')->trim()]),
        ]);
    }

    public function storeParentAccount(): RedirectResponse
    {
        $this->accountService->createParentAccount(
            code: (string) request('kode'),
            name: (string) request('nama'),
            direction: (string) request('tipe'),
        );

        return redirect('/accounting/parent-accounts')->with('success', 'Akun induk berhasil disimpan.');
    }

    public function updateParentAccount(string $uuid): RedirectResponse
    {
        $this->accountService->updateParentAccount(
            uuid: $uuid,
            code: (string) request('kode'),
            name: (string) request('nama'),
            direction: (string) request('tipe'),
        );

        return redirect('/accounting/parent-accounts')->with('success', 'Akun induk berhasil diubah.');
    }

    public function destroyParentAccount(string $uuid): RedirectResponse
    {
        $this->accountService->deleteAccount($uuid, current_branch_uuid(), current_company_uuid());

        return redirect('/accounting/parent-accounts')->with('success', 'Akun induk berhasil dihapus.');
    }

    public function showSubAccounts(Request $request): Response
    {
        return Inertia::render('Finance/Journal/presentations/views/SubAccountsIndex', [
            'filters' => $this->buildFilters($request, ['parent_uuid' => $request->string('parent_uuid')->trim()]),
        ]);
    }

    public function storeSubAccount(): RedirectResponse
    {
        $this->accountService->createSubAccount(
            parentCode: (string) request('parent_code'),
            code: (string) request('kode'),
            name: (string) request('nama'),
            direction: (string) request('tipe'),
        );

        return redirect('/accounting/sub-accounts')->with('success', 'Sub akun berhasil disimpan.');
    }

    public function updateSubAccount(string $uuid): RedirectResponse
    {
        $this->accountService->updateSubAccount(
            uuid: $uuid,
            code: (string) request('kode'),
            name: (string) request('nama'),
        );

        return redirect('/accounting/sub-accounts')->with('success', 'Sub akun berhasil diubah.');
    }

    public function destroySubAccount(string $uuid): RedirectResponse
    {
        $this->accountService->deleteAccount($uuid, current_branch_uuid(), current_company_uuid());

        return redirect('/accounting/sub-accounts')->with('success', 'Sub akun berhasil dihapus.');
    }

    public function showChartOfAccounts(Request $request): Response
    {
        return Inertia::render('Finance/Journal/presentations/views/ChartOfAccountsIndex', [
            'filters' => $this->buildFilters($request),
        ]);
    }

    public function showOpeningBalance(Request $request): Response
    {
        return Inertia::render('Finance/Journal/presentations/views/OpeningBalanceIndex', [
            'filters' => $this->buildFilters($request, ['period' => $request->string('period', now()->format('Y-m'))->trim()]),
        ]);
    }

    public function storeOpeningBalance(): RedirectResponse
    {
        return redirect('/accounting/opening-balance')->with('success', 'Saldo awal berhasil disimpan.');
    }

    public function showTransactions(Request $request): Response
    {
        return Inertia::render('Finance/Journal/presentations/views/AccountingTransactionsIndex', [
            'filters' => $this->buildFilters($request, ['period' => $request->string('period', now()->format('Y-m'))->trim()]),
        ]);
    }

    public function storeTransaction(): RedirectResponse
    {
        return redirect('/accounting/transactions')->with('success', 'Transaksi berhasil disimpan.');
    }

    public function destroyTransaction(string $uuid): RedirectResponse
    {
        $this->journalService->deleteJournal($uuid);

        return redirect('/accounting/transactions')->with('success', 'Transaksi berhasil dihapus.');
    }

    public function showJournals(Request $request): Response
    {
        return Inertia::render('Finance/Journal/presentations/views/JournalsIndex', [
            'filters' => $this->buildFilters($request, ['period' => $request->string('period', now()->format('Y-m'))->trim()]),
        ]);
    }

    public function showGeneralLedger(Request $request): Response
    {
        return Inertia::render('Finance/Journal/presentations/views/GeneralLedgerIndex', [
            'filters' => $this->buildFilters($request, ['period' => $request->string('period', now()->format('Y-m'))->trim()]),
        ]);
    }

    public function showTrialBalance(Request $request): Response
    {
        return Inertia::render('Finance/Journal/presentations/views/TrialBalanceIndex', [
            'filters' => ['period' => $request->string('period', now()->format('Y-m'))->trim()],
        ]);
    }

    public function exportTrialBalance(Request $request)
    {
        return redirect('/accounting/trial-balance?period='.$request->get('period', now()->format('Y-m')));
    }

    public function showBalanceSheet(Request $request): Response
    {
        return Inertia::render('Finance/Journal/presentations/views/BalanceSheetIndex', [
            'filters' => ['period' => $request->string('period', now()->format('Y-m'))->trim()],
        ]);
    }

    public function showIncomeStatement(Request $request): Response
    {
        return Inertia::render('Finance/Journal/presentations/views/IncomeStatementIndex', [
            'filters' => ['period' => $request->string('period', now()->format('Y-m'))->trim()],
        ]);
    }

    public function showPeriodAdjustments(Request $request): Response
    {
        return Inertia::render('Finance/Journal/presentations/views/PeriodAdjustmentsIndex', [
            'filters' => $this->buildFilters($request),
        ]);
    }

    public function showBalanceCheck(Request $request): Response
    {
        return Inertia::render('Finance/Journal/presentations/views/BalanceCheckIndex', [
            'filters' => $this->buildFilters($request),
        ]);
    }
}
