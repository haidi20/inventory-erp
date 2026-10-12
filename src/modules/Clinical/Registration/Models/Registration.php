<?php

namespace App\Modules\Clinical\Registration\Models;

use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use Database\Factories\Operational\RegistrationFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Operational\Master\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model Registration
 *
 * Mencatat kedatangan pasien ke klinik, poli tujuan, dan penomoran antrian layanan.
 */
class Registration extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasBranchScope, HasCompanyScope, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [

        'uuid',
        'id',
        'branch_uuid',
        'company_uuid',
        'queue_no',
        'payment_type',
        'bpjs_no',
        'poli',
        'patient_uuid',
        'doctor_uuid',
        'status',
        'registered_at',
        'notes',
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

    public function encounter()
    {
        return $this->hasOne(Encounter::class, 'registration_uuid', 'uuid');
    }

    public function paymentLabel(): string
    {
        return match ($this->payment_type) {
            'bpjs' => 'BPJS',
            'mandiri' => 'Mandiri',
            default => 'Mandiri',
        };
    }

    public function paymentColor(): string
    {
        return match ($this->payment_type) {
            'bpjs' => '#1d4ed8',
            'mandiri' => '#059669',
            default => '#059669',
        };
    }

    public function paymentBg(): string
    {
        return match ($this->payment_type) {
            'bpjs' => '#dbeafe',
            'mandiri' => '#d1fae5',
            default => '#d1fae5',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'waiting' => 'Menunggu',
            'in_progress' => 'Dipanggil',
            'done' => 'Selesai',
            'cancelled' => 'Batal',
            default => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'waiting' => '#6b7280',
            'in_progress' => '#f59e0b',
            'done' => '#22c55e',
            'cancelled' => '#ef4444',
            default => '#6b7280',
        };
    }
    protected static function newFactory(): Factory
    {
        return RegistrationFactory::new();
    }
}
