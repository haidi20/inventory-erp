<?php

namespace App\Modules\Analytics\Reports\Services;

use App\Modules\Analytics\Reports\Repositories\FinancialStatementMenuRepository;

class FinancialStatementMenuService
{
    public function __construct(private FinancialStatementMenuRepository $financialStatementMenuRepository)
    {
    }

    public function index(): array
    {
        $period = $this->financialStatementMenuRepository->getActivePeriod();
        $journalSummary = $this->financialStatementMenuRepository->getJournalSummary($period);

        return [
            'activePeriod' => $period,
            'stats' => [
                'account_count' => $this->financialStatementMenuRepository->getAccountCount(),
                'journal_total' => $journalSummary['total'],
                'journal_posted' => $journalSummary['posted'],
                'balance_coverage' => $this->financialStatementMenuRepository->getBalanceCoverage($period),
            ],
            'journalSummary' => $journalSummary,
            'reports' => [
                [
                    'title' => 'Neraca Saldo',
                    'description' => 'Melihat saldo akun per periode sebagai dasar rekonsiliasi jurnal.',
                    'url' => '/accounting/neraca-saldo',
                    'icon' => 'fa-scale-balanced',
                ],
                [
                    'title' => 'Neraca',
                    'description' => 'Memantau posisi aset, kewajiban, dan ekuitas pada periode aktif.',
                    'url' => '/accounting/neraca',
                    'icon' => 'fa-building-columns',
                ],
                [
                    'title' => 'Rugi Laba',
                    'description' => 'Melihat performa pendapatan dan beban untuk mengevaluasi profit klinik.',
                    'url' => '/accounting/laba-rugi',
                    'icon' => 'fa-chart-column',
                ],
                [
                    'title' => 'Buku Besar',
                    'description' => 'Menelusuri mutasi tiap akun untuk audit dan pengecekan sumber transaksi.',
                    'url' => '/accounting/bukubesar',
                    'icon' => 'fa-book-open',
                ],
            ],
        ];
    }
}

