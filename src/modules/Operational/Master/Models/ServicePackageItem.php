<?php

namespace App\Modules\Operational\Master\Models;

use App\Shared\Traits\Auditable;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasCompanyScope;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ServicePackageItem extends Model implements AuditableContract
{
    protected $dateFormat = 'U';

    use Auditable, Blameable, HasBranchScope, HasCompanyScope, HasSoftDeleteUnique, HasUuids;

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'service_package_items';

    protected $fillable = [
        'branch_uuid',
        'company_uuid',
        'service_package_uuid',
        'item_type',
        'item_uuid',
        'qty',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function servicePackage()
    {
        return $this->belongsTo(ServicePackage::class, 'service_package_uuid', 'uuid');
    }
}
