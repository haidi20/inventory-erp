<?php

namespace App\Modules\Clinical\MedicalRecord\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model Complaint
 *
 * Mencatat pengaduan dan saran dari pasien atau pihak lain.
 */
class Complaint extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasBranchScope, HasCompanyScope, HasFactory, HasUuids, HasSoftDeleteUnique;

    

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'branch_uuid',
        'company_uuid',
        'type',
        'subject',
        'content',
        'status',
        'is_anonymous',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
    ];

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'proses' => 'Diproses',
            'selesai' => 'Selesai',
            default => $this->status,
        };
    }
}

