<?php

namespace App\Modules\Clinical\Ancillary\Models;

use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model LabOrderItem
 *
 * Rincian parameter atau jenis tes laboratorium yang diminta untuk satu pasien.
 */
class LabOrderItem extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasUuids, HasSoftDeleteUnique, HasCompanyScope, HasBranchScope;


    

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [

        'uuid',
        'id',
        'company_uuid',
        'branch_uuid',
        'lab_order_uuid',
        'test_name',
        'test_code',
        'result',
        'unit',
        'normal_range',
        'flag',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function labOrder()
    {
        return $this->belongsTo(LabOrder::class, 'lab_order_uuid', 'uuid');
    }

    public function flagColor(): string
    {
        return match ($this->flag) {
            'high' => '#ef4444',
            'low' => '#3b82f6',
            'critical' => '#7c3aed',
            'normal' => '#059669',
            default => '#6b7280',
        };
    }
}




