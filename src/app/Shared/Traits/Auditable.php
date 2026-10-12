<?php

namespace App\Shared\Traits;

use OwenIt\Auditing\Auditable as SpatieAuditable;

/**
 * Wrapper trait untuk standar audit berbasis owen-it/laravel-auditing.
 */
trait Auditable
{
    use SpatieAuditable;
}
