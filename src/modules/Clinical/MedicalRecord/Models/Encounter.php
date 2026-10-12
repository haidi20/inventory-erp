<?php

namespace App\Modules\Clinical\MedicalRecord\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Operational\Master\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasCompanyScope;
use App\Modules\Clinical\Registration\Models\Registration;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model Encounter
 *
 * Mencatat setiap interaksi klinis antara pasien dan tenaga medis (pemeriksaan, konsultasi, diagnosis).
 */
class Encounter extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasBranchScope, HasCompanyScope, HasUuids, HasSoftDeleteUnique;


    

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [

        'uuid',
        'id',
        'branch_uuid',
        'company_uuid',
        'registration_uuid',
        'patient_uuid',
        'doctor_uuid',
        'complaint',
        'diagnosis',
        'notes',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /** @return BelongsTo<Registration, $this> */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_uuid', 'uuid');
    }

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_uuid', 'uuid');
    }

    /** @return BelongsTo<Employee, $this> */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'doctor_uuid', 'uuid');
    }

    /** @return HasMany<EncounterItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(EncounterItem::class, 'encounter_uuid', 'uuid');
    }

    public function getBranchIdAttribute(): ?string
    {
        return $this->branch_uuid;
    }
}


