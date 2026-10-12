<?php

namespace Tests\Unit\Repository;

use App\Modules\Clinical\Integration\Repositories\SatusehatRepository;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Unit Test untuk SatusehatRepository.
 * Menggunakan Http::fake() untuk menyimulasikan respon dari API Satusehat
 * tanpa melakukan koneksi internet sesungguhnya.
 */
class SatusehatRepositoryTest extends TestCase
{
    private SatusehatRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        
        config(['services.satusehat.base_url' => 'https://api-stg.kemkes.go.id']);
        config(['services.satusehat.auth_url' => 'https://api-stg.kemkes.go.id/auth']);
        config(['services.satusehat.client_id' => 'test-id']);
        config(['services.satusehat.client_secret' => 'test-secret']);

        $this->repository = new SatusehatRepository();
    }

    /** @test */
    public function it_can_fetch_token_successfully()
    {
        Http::fake([
            'api-stg.kemkes.go.id/auth' => Http::response([
                'access_token' => 'mock-token',
                'expires_in' => '3600',
                'token_type' => 'Bearer'
            ], 200)
        ]);

        $token = $this->repository->fetchToken();

        $this->assertNotNull($token);
        $this->assertEquals('mock-token', $token['access_token']);
    }

    /** @test */
    public function it_can_get_practitioner_by_nik()
    {
        Http::fake([
            'api-stg.kemkes.go.id/Practitioner*' => Http::response([
                'resourceType' => 'Bundle',
                'total' => 1,
                'entry' => [
                    [
                        'resource' => [
                            'resourceType' => 'Practitioner',
                            'id' => 'PRACT-123'
                        ]
                    ]
                ]
            ], 200)
        ]);

        $result = $this->repository->getPractitionerByNIK('fake-token', '1234567812345678');

        $this->assertNotNull($result);
        $this->assertEquals('PRACT-123', $result['id']);
    }

    /** @test */
    public function it_can_post_encounter_resource()
    {
        Http::fake([
            'api-stg.kemkes.go.id/Encounter' => Http::response([
                'resourceType' => 'Encounter',
                'id' => 'ENC-001'
            ], 201)
        ]);

        $payload = [
            'resourceType' => 'Encounter',
            'status' => 'arrived',
            'class' => ['code' => 'AMB']
        ];

        $result = $this->repository->postEncounter('fake-token', $payload);

        $this->assertNotNull($result);
        $this->assertEquals('ENC-001', $result['id']);
        
        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer fake-token') &&
                   $request['status'] === 'arrived';
        });
    }
}
