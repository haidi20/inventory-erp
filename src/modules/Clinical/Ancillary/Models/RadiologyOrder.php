<?php

namespace App\Modules\Clinical\Ancillary\Models;

use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model RadiologyOrder
 *
 * Permintaan pemeriksaan radiologi (X-Ray, USG, MRI) dari dokter ke unit radiologi.
 */
class RadiologyOrder extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasUuids, HasSoftDeleteUnique, HasCompanyScope, HasBranchScope;


    

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [

        'uuid',
        'id',
        'branch_uuid',
        'company_uuid',
        'order_no',
        'patient_uuid',
        'doctor_uuid',
        'modality',
        'body_part',
        'clinical_notes',
        'status',
        'findings',
        'impression',
        'radiologist_uuid',
        'ordered_at',
        'resulted_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
    ];

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_uuid', 'uuid');
    }

    /** @return BelongsTo<Employee, $this> */
    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_uuid', 'uuid');
    }

    public function radiologist()
    {
        return $this->belongsTo(User::class, 'radiologist_uuid', 'uuid');
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'selesai' => '#059669',
            'proses' => '#f59e0b',
            default => '#6b7280',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'selesai' => 'Selesai',
            'proses' => 'Diproses',
            default => 'Pending',
        };
    }

    public static function modalityLabel(string $m): string
    {
        return match ($m) {
            'rontgen' => 'Rontgen (X-Ray)',
            'usg' => 'USG',
            'ct_scan' => 'CT Scan',
            'mri' => 'MRI',
            'mammografi' => 'Mammografi',
            'fluoroskopi' => 'Fluoroskopi',
            default => ucfirst($m),
        };
    }
}



