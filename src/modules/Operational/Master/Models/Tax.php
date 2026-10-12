<?php

namespace App\Modules\Operational\Master\Models;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Shared\Traits\Auditable;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasSoftDeleteUnique;
use Database\Factories\Master\TaxFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Model Tax
 *
 * Definisi jenis pajak (PPN, PPh) dan tarifnya yang berlaku pada transaksi sistem.
 */
class Tax extends Model implements AuditableContract
{
    protected $dateFormat = 'U';

    use Auditable, Blameable, HasBranchScope, HasCompanyScope, HasFactory, HasSoftDeleteUnique, HasUuids;

    protected $table = 'taxes';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_uuid', 'uuid');
    }

    protected static function newFactory(): Factory
    {
        return TaxFactory::new();
    }
}
