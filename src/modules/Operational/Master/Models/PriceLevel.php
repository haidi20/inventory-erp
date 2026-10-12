<?php

namespace App\Modules\Operational\Master\Models;


use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\HasCompanyScope;use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Auditable;
use App\Shared\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\Master\PriceLevelFactory;


/**
 * Model PriceLevel
 *
 * Skema tingkatan harga (Retail, Grosir, Member) untuk fleksibilitas strategi harga jual.
 */
class PriceLevel extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use HasBranchScope;
    use HasCompanyScope;
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'price_levels';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_uuid', 'uuid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return PriceLevelFactory::new();
    }
}
