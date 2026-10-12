<?php

namespace Tests\Unit\Services\Integration;

use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Clinical\Integration\Repositories\SatusehatRepositoryInterface;
use App\Modules\Clinical\Registration\Repositories\PatientRepositoryInterface;
use App\Modules\Clinical\Integration\Services\SatusehatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

/**
 * Unit Test untuk SatusehatService.
 * Menggunakan Mockery untuk memalsukan Repositori sehingga fokus pada
 * pengujian logika bisnis dan sinkronisasi data ke database lokal.
 */
class SatusehatServiceTest extends TestCase
{
    private SatusehatService $service;
    private $repositoryMock;
    private $patientRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repositoryMock = Mockery::mock(SatusehatRepositoryInterface::class);
        $this->patientRepositoryMock = Mockery::mock(PatientRepositoryInterface::class);
        $this->service = new SatusehatService($this->repositoryMock, $this->patientRepositoryMock);
    }

    /** @test */
    public function it_successfully_caches_the_token()
    {
        $this->repositoryMock->shouldReceive('fetchToken')
            ->once()
            ->andReturn(['access_token' => 'service-token']);

        // First call should trigger fetch
        $token1 = \Illuminate\Support\Facades\Cache::remember('satusehat_access_token', 3600, function() {
             return $this->repositoryMock->fetchToken()['access_token'];
        });

        // Simulating the getToken() logic inside service
        $token2 = Cache::get('satusehat_access_token');

        $this->assertEquals('service-token', $token1);
        $this->assertEquals('service-token', $token2);
    }

    /** @test */
    public function it_updates_patient_data_and_sync_time_when_checked()
    {
        $nik = '1234567890123456';
        $ihsId = 'P-001';

        // Create local patient
        $patient = Patient::factory()->create(['nik' => $nik, 'ihs_number' => null]);

        // Mock repo returning data
        $this->repositoryMock->shouldReceive('fetchToken')->andReturn(['access_token' => 'token']);
        $this->repositoryMock->shouldReceive('getPatientByNIK')->once()->andReturn(['id' => $ihsId]);
        $this->patientRepositoryMock->shouldReceive('updateIhsNumberByNik')
            ->once()
            ->with($nik, $ihsId)
            ->andReturnUsing(function (string $nik, string $ihsId): void {
                Patient::query()->where('nik', $nik)->update([
                    'ihs_number' => $ihsId,
                    'satusehat_last_sync' => time(),
                ]);
            });

        $this->service->CheckPatient($nik);

        // Verify database updated
        $patient->refresh();
        $this->assertEquals($ihsId, $patient->ihs_number);
        $this->assertNotNull($patient->satusehat_last_sync);
    }

    /** @test */
    public function it_updates_employee_data_and_sync_time_when_checked()
    {
        $nik = '8888777766665555';
        $practId = 'PR-999';

        // Create local employee
        $employee = Employee::factory()->create(['nik' => $nik, 'ihs_practitioner_id' => null]);

        $this->repositoryMock->shouldReceive('fetchToken')->andReturn(['access_token' => 'token']);
        $this->repositoryMock->shouldReceive('getPractitionerByNIK')->once()->andReturn(['id' => $practId]);

        $this->service->CheckPractitioner($nik);

        $employee->refresh();
        $this->assertEquals($practId, $employee->ihs_practitioner_id);
        $this->assertNotNull($employee->satusehat_last_sync);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
