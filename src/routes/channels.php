<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{uuid}', function ($user, $uuid) {
    return $user->uuid === $uuid;
});

Broadcast::channel('company.{uuid}', function ($user, $uuid) {
    return $user->belongsToCompany($uuid);
});

Broadcast::channel('branch.{uuid}', function ($user, $uuid) {
    return $user->belongsToBranch($uuid);
});