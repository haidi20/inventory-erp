<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = Illuminate\Support\Facades\DB::table('users')
    ->where('email', 'superadmin@klinik.local')
    ->first(['email', 'username', 'is_active', 'password']);

dump($user);
dump(Illuminate\Support\Facades\Auth::validate([
    'email' => 'superadmin@klinik.local',
    'password' => 'superadmin',
]));
