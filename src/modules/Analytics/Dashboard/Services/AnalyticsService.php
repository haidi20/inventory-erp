<?php

namespace App\Modules\Analytics\Dashboard\Services;

use App\Modules\Analytics\Reports\Entities\AnalyticsFilterEntity;
use App\Modules\Analytics\Dashboard\Repositories\AnalyticsRepository;

class AnalyticsService
{
    public function __construct(private AnalyticsRepository $analyticsRepository)
    {
    }

    public function overview(AnalyticsFilterEntity $entity): array
    {
        $metrics = $this->analyticsRepository->getOverviewMetrics($entity);
        $series = $this->analyticsRepository->getMonthlySeries($entity, 6);

        return array_merge($metrics, [
            'pointsService' => $this->buildPolylinePoints($series['service']),
            'pointsProduct' => $this->buildPolylinePoints($series['product']),
            'topDoctors' => $this->analyticsRepository->getDoctorPerformance($entity, 5),
        ]);
    }

    public function doctorProfit(AnalyticsFilterEntity $entity): array
    {
        return [
            'doctorMetrics' => $this->analyticsRepository->getDoctorPerformance($entity),
        ];
    }

    public function serviceProfit(AnalyticsFilterEntity $entity): array
    {
        return [
            'serviceMetrics' => $this->analyticsRepository->getServiceProfitStats($entity),
        ];
    }

    public function patientProfit(AnalyticsFilterEntity $entity): array
    {
        return [
            'patientMetrics' => $this->analyticsRepository->getPatientRevenue($entity),
        ];
    }

    public function productMargin(AnalyticsFilterEntity $entity): array
    {
        return [
            'products' => $this->analyticsRepository->getProductsByMargin($entity),
        ];
    }

    public function bestSellingServices(AnalyticsFilterEntity $entity): array
    {
        return [
            'topServices' => $this->analyticsRepository->getTopServices($entity),
        ];
    }

    public function profitablePatients(AnalyticsFilterEntity $entity): array
    {
        return [
            'patientMetrics' => $this->analyticsRepository->getPatientRevenue($entity),
        ];
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
