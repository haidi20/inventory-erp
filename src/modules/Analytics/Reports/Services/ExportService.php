<?php

namespace App\Modules\Analytics\Reports\Services;

/**
 * ExportService
 *
 * Port dari BE: export_service.go. Serializer ringan untuk laporan keuangan
 * ke format CSV — Laporan Laba Rugi (Income Statement), Neraca (Balance Sheet),
 * dan Buku Besar (Ledger). Mengembalikan string CSV siap diunduh.
 */
use App\Modules\Analytics\Reports\Entities\AnalyticsFilterEntity;

class ExportService
{
    /**
     * Export Laporan Laba Rugi ke CSV.
     */
    public function exportIncomeStatementToCsv(array $data): string
    {
        $buffer = fopen('php://temp', 'r+');
        fputcsv($buffer, ['Code', 'Name', 'Account Type', 'Revenue', 'Expense']);
        foreach ($data as $row) {
            fputcsv($buffer, [
                (string) ($row['code'] ?? ''),
                (string) ($row['name'] ?? ''),
                (string) ($row['account_type'] ?? ''),
                number_format((float) ($row['revenue'] ?? 0), 2, '.', ''),
                number_format((float) ($row['expense'] ?? 0), 2, '.', ''),
            ]);
        }
        rewind($buffer);
        $content = stream_get_contents($buffer);
        fclose($buffer);
        return (string) $content;
    }

    /**
     * Export Neraca (Balance Sheet) ke CSV.
     */
    public function exportBalanceSheetToCsv(array $data): string
    {
        $buffer = fopen('php://temp', 'r+');
        fputcsv($buffer, ['Total Assets', 'Total Liabilities', 'Total Equity']);
        foreach ($data as $row) {
            fputcsv($buffer, [
                number_format((float) ($row['total_assets'] ?? 0), 2, '.', ''),
                number_format((float) ($row['total_liabilities'] ?? 0), 2, '.', ''),
                number_format((float) ($row['total_equity'] ?? 0), 2, '.', ''),
            ]);
        }
        rewind($buffer);
        $content = stream_get_contents($buffer);
        fclose($buffer);
        return (string) $content;
    }

    /**
     * Export Buku Besar (Ledger) ke CSV.
     */
    public function exportLedgerToCsv(array $data): string
    {
        $buffer = fopen('php://temp', 'r+');
        
        fputcsv($buffer, ['Account Code', (string) ($data['account_code'] ?? '')]);
        fputcsv($buffer, ['Account Name', (string) ($data['account_name'] ?? '')]);
        fputcsv($buffer, ['Opening Balance', number_format((float) ($data['opening_balance'] ?? 0), 2, '.', '')]);
        fputcsv($buffer, ['']); // spacer

        fputcsv($buffer, ['Date', 'Reference No', 'Description', 'Debit', 'Credit', 'Balance']);
        foreach (($data['transactions'] ?? []) as $tx) {
            $date = $tx['journal_date'] ?? null;
            if (is_numeric($date)) {
                $dateStr = date('Y-m-d', (int) $date);
            } else {
                $dateStr = (string) ($date ?? '');
            }

            fputcsv($buffer, [
                $dateStr,
                (string) ($tx['reference_no'] ?? ''),
                (string) ($tx['description'] ?? ''),
                number_format((float) ($tx['debit'] ?? 0), 2, '.', ''),
                number_format((float) ($tx['credit'] ?? 0), 2, '.', ''),
                number_format((float) ($tx['balance'] ?? 0), 2, '.', ''),
            ]);
        }

        fputcsv($buffer, ['']);
        fputcsv($buffer, ['Closing Balance', number_format((float) ($data['closing_balance'] ?? 0), 2, '.', '')]);

        rewind($buffer);
        $content = stream_get_contents($buffer);
        fclose($buffer);
        return (string) $content;
    }
}
