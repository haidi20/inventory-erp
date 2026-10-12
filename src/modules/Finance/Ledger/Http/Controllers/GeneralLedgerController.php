<?php

namespace App\Modules\Finance\Ledger\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Ledger\Entities\GeneralLedgerFilterEntity;
use App\Modules\Finance\Ledger\Exports\GeneralLedgerExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class GeneralLedgerController extends Controller
{
    public function __construct(
        protected GeneralLedgerService $ledgerService
    ) {}

    public function index(Request $request)
    {
        $entity = $this->buildFilterEntity($request);

        return $this->apiResource($this->ledgerService->listGeneralLedgers($entity));
    }

    public function exportExcel(Request $request)
    {
        $report = $this->ledgerService->getLedgerReport($this->buildFilterEntity($request, [
            'date_from' => $request->query('date_from', now()->startOfMonth()->toDateString()),
            'date_to' => $request->query('date_to', now()->toDateString()),
        ]));

        return Excel::download(new GeneralLedgerExport($report), 'buku_besar.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $dateFrom = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', now()->toDateString());

        $report = $this->ledgerService->getLedgerReport($this->buildFilterEntity($request, [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]));

        $ledgers = $report['transactions'];
        $account = (object)$report['account_info']; // Cast to object for blade compatibility if needed

        $pdf = Pdf::loadView('reports.general_ledger_pdf', [
            'ledgers' => collect($ledgers)->map(fn($item) => (object)$item), // Cast items to object for blade compatibility
            'account' => $account,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ])->setPaper('a4', 'landscape');

        return $pdf->download('buku_besar.pdf');
    }

    private function buildFilterEntity(Request $request, array $overrides = []): GeneralLedgerFilterEntity
    {
        $user = auth()->user();

        return GeneralLedgerFilterEntity::fromArray(array_merge([
            'account_uuid' => $request->query('account_uuid'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'period_uuid' => $request->query('period_uuid'),
            'branch_uuid' => current_branch_uuid() ?? $user?->active_branch_uuid,
            'company_uuid' => current_company_uuid() ?? $user?->active_company_uuid,
            'page' => $request->integer('page', 1),
            'per_page' => $request->integer('per_page', 50),
        ], $overrides));
    }
}
