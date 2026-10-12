<?php

namespace App\Modules\Operational\Sales\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransaksiController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function showSalesList(Request $request): Response
    {
        return Inertia::render('Operational/Sales/presentations/views/SalesTransactionIndex', [
            'filters' => $this->buildFilters($request),
        ]);
    }

    public function showPurchaseList(Request $request): Response
    {
        return Inertia::render('Operational/Sales/presentations/views/PurchaseTransactionIndex', [
            'filters' => $this->buildFilters($request),
        ]);
    }

    public function showExaminationList(Request $request): Response
    {
        return Inertia::render('Operational/Sales/presentations/views/ExaminationTransactionIndex', [
            'filters' => $this->buildFilters($request),
        ]);
    }

    public function showReceivableList(Request $request): Response
    {
        return Inertia::render('Operational/Sales/presentations/views/ReceivableTransactionIndex', [
            'filters' => $this->buildFilters($request),
        ]);
    }

    public function showPayableList(Request $request): Response
    {
        return Inertia::render('Operational/Sales/presentations/views/PayableTransactionIndex', [
            'filters' => $this->buildFilters($request),
        ]);
    }

    public function showSalesCreateForm(): Response
    {
        return Inertia::render('Operational/Sales/presentations/views/SalesTransactionIndex', [
            'filters' => [],
            'mode'    => 'create',
        ]);
    }

    public function showSaleDetail(string $saleUuid): Response
    {
        return Inertia::render('Operational/Sales/presentations/views/SalesTransactionIndex', [
            'filters'   => [],
            'sale_uuid' => $saleUuid,
            'mode'      => 'detail',
        ]);
    }

    public function showExaminationForm(string $registrationUuid): Response
    {
        return Inertia::render('Operational/Sales/presentations/views/ExaminationTransactionIndex', [
            'filters'           => [],
            'registration_uuid' => $registrationUuid,
            'mode'              => 'form',
        ]);
    }

    private function buildFilters(Request $request): array
    {
        return [
            'search'     => (string) $request->string('search')->trim(),
            'date_from'  => (string) $request->string('date_from')->trim(),
            'date_to'    => (string) $request->string('date_to')->trim(),
            'status'     => (string) $request->string('status')->trim(),
            'page'       => $request->integer('page', 1),
            'per_page'   => $request->integer('per_page', 10),
        ];
    }
}
