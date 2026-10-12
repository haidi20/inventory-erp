<?php

namespace App\Modules\Analytics\Reports\Services;

use App\Modules\Finance\Journal\Entities\FinancialReportEntity;
use App\Modules\Analytics\Reports\Repositories\ReportRepository;

class ReportService
{
    public function __construct(private ReportRepository $reportRepository)
    {
    }

    public function cashFlow(FinancialReportEntity $entity): array
    {
        $data = $this->reportRepository->getCashFlowData($entity);
        $data['cashNet'] = $data['cashIn'] - $data['cashOut'];

        return $data;
    }

    public function examinationReport(FinancialReportEntity $entity): array
    {
        return $this->reportRepository->getExaminationReportData($entity);
    }

    public function pointOfSaleSalesReport(FinancialReportEntity $entity): array
    {
        return $this->reportRepository->getSalesPosReportData($entity);
    }

    public function doctorIncomeReport(FinancialReportEntity $entity): array
    {
        return [
            'doctorMetrics' => $this->reportRepository->getDoctorIncomeStats($entity),
        ];
    }

    public function productVsServiceReport(FinancialReportEntity $entity): array
    {
        $series = $this->reportRepository->getMonthlySeries($entity, 6);

        return [
            'pointsService' => $this->buildPolylinePoints($series['service']),
            'pointsProduct' => $this->buildPolylinePoints($series['product']),
        ];
    }

    public function operationalExpenseReport(FinancialReportEntity $entity): array
    {
        return $this->reportRepository->getOperationalExpenseData($entity);
    }

    private function buildPolylinePoints(array $values): string
    {
        $count = count($values);

        if ($count === 0) {
            return '';
        }

        $width = 1000;
        $height = 220;
        $max = max($values) ?: 1;
        $step = $count > 1 ? $width / ($count - 1) : $width;
        $points = [];

        foreach ($values as $index => $value) {
            $points[] = round($index * $step, 2) . ',' . round($height - ($value / $max) * $height, 2);
        }

        return implode(' ', $points);
    }
}
