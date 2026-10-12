<?php

namespace App\Shared\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * Trait Blameable
 * 
 * Trait ini secara otomatis mengisi kolom audit (created_by, updated_by, deleted_by)
 * dengan UUID pengguna yang sedang login saat terjadi event pada model.
 */
trait Blameable
{
    public static function bootBlameable()
    {
        static::creating(function ($model) {
            $user_id = auth()->id() ?? auth('api')->id();
            if ($user_id) {
                $model->created_by = $user_id;
                $model->updated_by = $user_id;
            }
        });

        static::updating(function ($model) {
            $user_id = auth()->id() ?? auth('api')->id();
            if ($user_id) {
                $model->updated_by = $user_id;
            }
        });

        static::deleting(function ($model) {
            $user_id = auth()->id() ?? auth('api')->id();
            if ($user_id && in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($model))) {
                $model->deleted_by = $user_id;
                $model->save();
            }
        });
    }
}

