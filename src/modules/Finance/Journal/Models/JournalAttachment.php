<?php

namespace App\Modules\Finance\Journal\Models;

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
 * Model JournalAttachment
 *
 * Lampiran digital (scan nota/bukti transfer) sebagai bukti fisik pendukung jurnal akuntansi.
 */
class JournalAttachment extends Model implements AuditableContract {
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
        'journal_uuid',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_uuid', 'uuid');
    }
}





