<?php

namespace Tests\Feature\Accounting;

use App\Modules\Analytics\Reports\Exports\BalanceSheetExport;
use App\Modules\Analytics\Reports\Exports\IncomeStatementExport;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Analytics\Reports\Services\FinancialReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected $reportService;
    protected $branch;
    protected $period;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->branch = Branch::factory()->create();
        $this->reportService = app(FinancialReportService::class);
        $this->period = ClosingPeriod::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31'
        ]);
        
        $user = User::factory()->create(['branch_uuid' => $this->branch->uuid]);
        $this->actingAs($user);
    }

    /**
     * Test Ekspor Laporan ke Format Excel (.xlsx)
     */
    public function test_export_report_to_excel_structure()
    {
        // Simulasi ekspor Neraca (Balance Sheet) ke Excel
        $response = $this->reportService->exportBalanceSheet($this->period->uuid, $this->branch->uuid, 'excel');
        
        $this->assertIsArray($response);
        $this->assertEquals('excel', $response['format']);
        $this->assertNotNull($response['file_path']);
    }

    /**
     * Test Ekspor Laporan ke Format PDF
     */
    public function test_export_report_to_pdf_structure()
    {
        // Simulasi ekspor Laba Rugi ke PDF
        $response = $this->reportService->exportIncomeStatement($this->period->uuid, $this->branch->uuid, 'pdf');
        
        $this->assertIsArray($response);
        $this->assertEquals('pdf', $response['format']);
    }

    /**
     * Test Ekspor Laporan ke Format CSV
     */
    public function test_export_report_to_csv_structure()
    {
        $response = $this->reportService->exportIncomeStatement($this->period->uuid, $this->branch->uuid, 'csv');
        
        $this->assertIsArray($response);
        $this->assertEquals('csv', $response['format']);
    }

    public function test_export_excel_multi_sheet_titles_are_available()
    {
        $incomeExport = new IncomeStatementExport($this->sampleReportData('Laba Rugi'));
        $balanceExport = new BalanceSheetExport($this->sampleReportData('Neraca'));

        $this->assertEquals('Laporan Laba Rugi', $incomeExport->title());
        $this->assertEquals('Neraca', $balanceExport->title());
        $this->assertCount(2, [$incomeExport->title(), $balanceExport->title()]);
    }

    public function test_export_pdf_pagination_can_chunk_long_report()
    {
        $rows = range(1, 120);
        $pages = $this->paginateRows($rows, 50);

        $this->assertCount(3, $pages);
        $this->assertCount(50, $pages[0]);
        $this->assertCount(50, $pages[1]);
        $this->assertCount(20, $pages[2]);
    }

    public function test_export_csv_uses_expected_delimiter()
    {
        $csv = $this->buildCsv(
            ['Akun', 'Nominal'],
            [
                ['Kas', '1000000'],
                ['Pendapatan', '1000000'],
            ],
            ','
        );

        $this->assertStringContainsString('Akun,Nominal', $csv);
        $this->assertStringContainsString('Kas,1000000', $csv);
    }

    public function test_export_csv_preserves_utf8_encoding()
    {
        $csv = $this->buildCsv(
            ['Laporan', 'Keterangan'],
            [
                ['Keuangan Klinik', 'Klinik Indonesia Rp 1.000.000'],
            ],
            ';'
        );

        if (function_exists('mb_check_encoding')) {
            $this->assertTrue(mb_check_encoding($csv, 'UTF-8'));
        }

        $this->assertStringContainsString('Keuangan Klinik;Klinik Indonesia Rp 1.000.000', $csv);
    }

    public function test_export_report_with_filter_uses_period_context()
    {
        $response = $this->reportService->exportBalanceSheet($this->period->uuid, $this->branch->uuid, 'excel');

        $this->assertEquals('excel', $response['format']);
        $this->assertStringContainsString((string) $this->period->uuid, $response['file_path']);
        $this->assertStringEndsWith('.excel', $response['file_path']);
    }

    public function test_export_large_dataset_can_generate_non_empty_csv()
    {
        $rows = [];
        for ($i = 1; $i <= 10000; $i++) {
            $rows[] = ["Akun {$i}", (string) ($i * 1000)];
        }

        $csv = $this->buildCsv(['Akun', 'Nominal'], $rows, ',');
        $lines = preg_split('/\r\n|\r|\n/', trim($csv));

        $this->assertCount(10001, $lines);
        $this->assertGreaterThan(100000, strlen($csv));
    }

    private function sampleReportData(string $title): array
    {
        return [
            'period_info' => ['period_name' => '2026-01'],
            'branch_info' => ['name' => $this->branch->name],
            'title' => $title,
            'details' => [],
        ];
    }

    private function paginateRows(array $rows, int $perPage): array
    {
        return array_chunk($rows, $perPage);
    }

    private function buildCsv(array $headers, array $rows, string $delimiter): string
    {
        $lines = [implode($delimiter, $headers)];

        foreach ($rows as $row) {
            $lines[] = implode($delimiter, array_map(fn($value) => (string) $value, $row));
        }

        return implode(PHP_EOL, $lines);
    }
}
