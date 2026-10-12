<?php

namespace App\Modules\Clinical\Registration\Models;

use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Clinical\Ancillary\Models\LabOrder;
use App\Modules\Clinical\Ancillary\Models\RadiologyOrder;
use App\Shared\Traits\Auditable;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\HasSoftDeleteUnique;
use Database\Factories\Master\PatientFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasCompanyScope;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Model Patient
 *
 * Data master medis dan personal pasien yang menjadi subjek utama layanan klinik.
 */
class Patient extends Model implements AuditableContract
{
    protected $dateFormat = 'U';

    use Auditable, Blameable, HasBranchScope, HasCompanyScope, HasFactory, HasSoftDeleteUnique, HasUuids;

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [

        'uuid',
        'id',
        'branch_uuid',
        'company_uuid',
        'medical_record_no',
        'name',
        'birth_date',
        'gender',
        'phone',
        'email',
        'address',
        'bpjs_number',
        'nik',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'patient_uuid', 'uuid')->orderByDesc('registered_at');
    }

    public function encounters()
    {
        return $this->hasMany(Encounter::class, 'patient_uuid', 'uuid')->orderByDesc('uuid');
    }

    public function labOrders()
    {
        return $this->hasMany(LabOrder::class, 'patient_uuid', 'uuid')->orderByDesc('ordered_at');
    }

    public function radiologyOrders()
    {
        return $this->hasMany(RadiologyOrder::class, 'patient_uuid', 'uuid')->orderByDesc('ordered_at');
    }

    public function ageString(): string
    {
        if (! $this->birth_date) {
            return '-';
        }
        $dob = Carbon::parse($this->birth_date);
        $now = Carbon::now();
        $y = (int) $dob->diffInYears($now);
        $m = (int) $dob->copy()->addYears($y)->diffInMonths($now);
        $d = (int) $dob->copy()->addYears($y)->addMonths($m)->diffInDays($now);
        $parts = [];
        if ($y) {
            $parts[] = $y.' Tahun';
        }
        if ($m) {
            $parts[] = $m.' Bulan';
        }
        if ($d || ! $parts) {
            $parts[] = $d.' Hari';
        }

        return implode(' ', $parts);
    }

    public function genderLabel(): string
    {
        return match ($this->gender) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '-',
        };
    }

    protected static function newFactory(): Factory
    {
        return PatientFactory::new();
    }
}
