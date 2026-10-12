<?php

namespace App\Modules\Operational\Master\Models;


use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\HasCompanyScope;use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;

class ProductImage extends Model implements AuditableContract
{
    protected $dateFormat = 'U';

    use HasBranchScope;
    use HasCompanyScope;
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;

    protected $table = 'item_images';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function item()
    {
        return $this->belongsTo(Product::class, 'item_uuid', 'uuid');
    }
}
