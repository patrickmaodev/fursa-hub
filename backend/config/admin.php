<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Initial administrator (php artisan admin:create)
    |--------------------------------------------------------------------------
    */

    'name' => env('ADMIN_NAME'),

    'email' => env('ADMIN_EMAIL'),

    'password' => env('ADMIN_PASSWORD'),

    'role' => env('ADMIN_ROLE', 'admin'),

];
