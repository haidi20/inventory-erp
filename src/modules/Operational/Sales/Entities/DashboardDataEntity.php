<?php

namespace App\Modules\Operational\Sales\Entities;

class DashboardDataEntity
{
    public function __construct(
        public int $activeQueueCount,
        public float $serviceRevenueToday,
        public int $completedServiceCountToday,
        public float $posRevenueToday,
        public float $posItemCountToday,
        public array $dailyChartLabels,
        public array $dailyServiceRevenueSeries,
        public array $dailyPosRevenueSeries,
        public array $monthlyChartLabels,
        public array $monthlyVisitSeries,
        public float $serviceRevenueDistribution,
        public float $posRevenueDistribution,
        public object $latestRegistrations, // Collection
        public object $latestSales, // Collection
        public int $patientCount,
        public array $quickMenus
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            activeQueueCount: $data['activeQueueCount'] ?? 0,
            serviceRevenueToday: (float) ($data['serviceRevenueToday'] ?? 0),
            completedServiceCountToday: $data['completedServiceCountToday'] ?? 0,
            posRevenueToday: (float) ($data['posRevenueToday'] ?? 0),
            posItemCountToday: (float) ($data['posItemCountToday'] ?? 0),
            dailyChartLabels: $data['dailyChartLabels'] ?? [],
            dailyServiceRevenueSeries: $data['dailyServiceRevenueSeries'] ?? [],
            dailyPosRevenueSeries: $data['dailyPosRevenueSeries'] ?? [],
            monthlyChartLabels: $data['monthlyChartLabels'] ?? [],
            monthlyVisitSeries: $data['monthlyVisitSeries'] ?? [],
            serviceRevenueDistribution: (float) ($data['serviceRevenueDistribution'] ?? 0),
            posRevenueDistribution: (float) ($data['posRevenueDistribution'] ?? 0),
            latestRegistrations: $data['latestRegistrations'],
            latestSales: $data['latestSales'],
            patientCount: $data['patientCount'] ?? 0,
            quickMenus: $data['quickMenus'] ?? []
        );
    }
}

