<?php

namespace App\Modules\Finance\Journal\Http\Controllers;

use App\Shared\Support\ExceptionLogger;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Exports\JournalExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function __construct(
        protected JournalService $journalService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        return $this->apiResource(
            $this->journalService->listJournals($branchUuid, $companyUuid)
        );
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();
        $data = $request->validate([
            'reference_no' => 'required|string|max:50',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'reference_model' => 'nullable|string',
            'reference_type' => 'nullable|string',
            'reference_uuid' => 'required|uuid',
            'status' => 'nullable|in:draft,posted',
            'lines' => 'required|array|min:2',
            'lines.*.account_uuid' => 'required|uuid',
            'lines.*.debit' => 'numeric|min:0',
            'lines.*.credit' => 'numeric|min:0',
            'lines.*.description' => 'nullable|string',
        ]);

        try {
            // Inject branch_uuid dari user yang login
            $this->journalService->validateBeforeCreate($data, $branchUuid, $companyUuid);

            $data['branch_uuid'] = $branchUuid;
            $data['company_uuid'] = $companyUuid;

            $results = $this->journalService->createManyByArray([$data]);
            $journal = $results[0]->load('lines');
            
            // Log audit jika diperlukan (method audit diasumsikan ada di base Controller atau trait)
            if (method_exists($this, 'audit')) {
                $this->audit('journal.create', $journal, null, $journal->toArray());
            }

            return $this->apiResource($journal)->response()->setStatusCode(201);
        } catch (\Exception $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function show(Request $request, string $journal)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();
        $result = $this->journalService->findJournal($journal, $branchUuid, $companyUuid);

        abort_unless($result, 404);

        return $this->apiResource(
            $result
        );
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'branch_uuid', 'status', 'keyword', 'period_uuid']);
        if (!isset($filters['branch_uuid'])) {
            $filters['branch_uuid'] = current_branch_uuid();
        }

        $journals = $this->journalService->getJournalReport($filters);

        return Excel::download(new JournalExport($journals), 'jurnal_umum.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'branch_uuid', 'status', 'keyword', 'period_uuid']);
        if (!isset($filters['branch_uuid'])) {
            $filters['branch_uuid'] = current_branch_uuid();
        }

        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        
        $journals = $this->journalService->getJournalReport($filters);

        $pdf = Pdf::loadView('reports.journal_pdf', compact('journals', 'dateFrom', 'dateTo'));
        return $pdf->download('jurnal_umum.pdf');
    }
}
