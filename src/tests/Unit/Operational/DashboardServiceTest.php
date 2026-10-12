<?php

namespace Tests\Unit\Operational;

use App\Modules\Operational\Sales\Services\DashboardService;
use App\Modules\Operational\Sales\Repositories\DashboardRepository;
use App\Modules\Operational\Sales\Entities\DashboardDataEntity;
use App\Modules\Core\Identity\Entities\UserEntity;
use App\Shared\Support\CompanyContext;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    protected $service;
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = $this->createMock(DashboardRepository::class);
        $this->service = new DashboardService($this->repository);
    }

    public function test_get_dashboard_data_populates_entity_correctly()
    {
        $companyUuid = 'company-test-uuid';

        $this->repository->method('getActiveQueueCount')->with($companyUuid)->willReturn(5);
        $this->repository->method('getTodayServiceRevenue')->with($companyUuid)->willReturn(1500000.0);
        $this->repository->method('getCompletedServiceCountToday')->with($companyUuid)->willReturn(10);
        $this->repository->method('getTodayPosRevenue')->with($companyUuid)->willReturn(2000000.0);
        $this->repository->method('getTodayPosItemCount')->with($companyUuid)->willReturn(15.0);
        
        $this->repository->method('getDailyRevenueStats')
            ->with($companyUuid, 30)
            ->willReturn([
                'dailyChartLabels' => [],
                'dailyServiceRevenueSeries' => [],
                'dailyPosRevenueSeries' => [],
            ]);

        $this->repository->method('getMonthlyVisitStats')
            ->with($companyUuid, 12)
            ->willReturn([
                'monthlyChartLabels' => [],
                'monthlyVisitSeries' => [],
            ]);

        $this->repository->method('getMonthlyRevenueDistribution')
            ->with($companyUuid)
            ->willReturn([
                'serviceRevenueDistribution' => 5000.0,
                'posRevenueDistribution' => 15000.0,
            ]);

        $this->repository->method('getLatestRegistrations')
            ->with($companyUuid, 8)
            ->willReturn(collect([]));

        $this->repository->method('getLatestSales')
            ->with($companyUuid, 6)
            ->willReturn(collect([]));

        $this->repository->method('getPatientCount')->with($companyUuid)->willReturn(100);

        $userEntity = UserEntity::fromArray([
            'uuid' => 'user-test-uuid',
            'company_uuid' => $companyUuid,
            'branch_uuid' => 'branch-test-uuid',
        ]);
        // buildQuickMenus() memfilter menu via UserEntity::can() berdasarkan
        // permissions. fromArray() tidak mengisi permissions, jadi set eksplisit
        // agar quickMenus tidak kosong (perilaku service yang benar).
        $userEntity->permissions = ['himbauan.view', 'settings.view'];

        // DashboardService membaca company context dari request via helper
        // current_company_uuid() (bukan dari UserEntity) -- sesuai migrasi
        // konteks multi-org. Set context seperti yang dilakukan middleware
        // ResolveCompanyContext di production.
        request()->attributes->set(
            'company_context',
            new CompanyContext($companyUuid, 'branch-test-uuid')
        );

        $data = $this->service->getDashboardData($userEntity);

        $this->assertInstanceOf(DashboardDataEntity::class, $data);
        $this->assertEquals(5, $data->activeQueueCount);
        $this->assertEquals(1500000.0, $data->serviceRevenueToday);
        $this->assertEquals(2000000.0, $data->posRevenueToday);
        $this->assertEquals(100, $data->patientCount);
        $this->assertIsArray($data->quickMenus);
        $this->assertNotEmpty($data->quickMenus);
    }
}
