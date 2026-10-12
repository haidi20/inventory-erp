<?php

namespace Tests\Feature\Operational;

use App\Modules\Core\Organization\Models\Branch;
use Database\Seeders\V1\ClinicalServiceCatalogSeeder;
use Database\Seeders\V1\DoctorConsultationWorkflowSeeder;
use Database\Seeders\V1\DokterDanRoleSeeder;
use Database\Seeders\V1\PatientsSeeder;
use Database\Seeders\V1\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorConsultationWorkflowSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_consultation_workflow_seeder_populates_relevant_clinic_process_data(): void
    {
        $this->seed([
            RbacSeeder::class,
            DokterDanRoleSeeder::class,
            PatientsSeeder::class,
            ClinicalServiceCatalogSeeder::class,
            DoctorConsultationWorkflowSeeder::class,
        ]);

        $demoBranch = Branch::query()->where('short_name', 'KDM')->firstOrFail();

        $this->assertDatabaseHas('registrations', [
            'queue_no' => 'A-018',
            'status' => 'done',
            'poli' => 'Penyakit Dalam',
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
        ]);

        $this->assertDatabaseHas('registrations', [
            'queue_no' => 'A-016',
            'status' => 'in_progress',
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
        ]);

        $this->assertDatabaseHas('lab_orders', [
            'order_no' => 'LAB-KDM-20260317-001',
            'status' => 'pending',
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
        ]);

        $this->assertDatabaseHas('lab_orders', [
            'order_no' => 'LAB-KDM-20260317-002',
            'status' => 'proses',
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
        ]);

        $this->assertDatabaseHas('lab_orders', [
            'order_no' => 'LAB-KDM-20260316-001',
            'status' => 'selesai',
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
        ]);

        $this->assertDatabaseHas('radiology_orders', [
            'order_no' => 'RAD-KDM-20260317-001',
            'status' => 'pending',
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
        ]);

        $this->assertDatabaseHas('radiology_orders', [
            'order_no' => 'RAD-KDM-20260317-002',
            'status' => 'proses',
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
        ]);

        $this->assertDatabaseHas('radiology_orders', [
            'order_no' => 'RAD-KDM-20260316-001',
            'status' => 'selesai',
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
        ]);

        $this->assertDatabaseHas('lab_order_items', [
            'test_name' => 'HbA1c',
            'result' => '8.4',
            'flag' => 'high',
        ]);

        $this->assertDatabaseHas('encounters', [
            'diagnosis' => 'Diabetes melitus tipe 2 tidak terkontrol',
            'status' => 'done',
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
        ]);

        $this->assertDatabaseHas('encounters', [
            'diagnosis' => 'Osteoartritis genu dextra',
            'status' => 'done',
            'branch_uuid' => $demoBranch->uuid,
            'company_uuid' => $demoBranch->company_uuid,
        ]);
    }
}
