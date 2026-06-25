<?php

return [
    'base_url'      => env('DJOMY_BASE_URL', 'https://sandbox-api.djomy.africa'),
    'client_id'     => env('DJOMY_CLIENT_ID', ''),
    'client_secret' => env('DJOMY_CLIENT_SECRET', ''),
    'country_code'  => env('DJOMY_COUNTRY_CODE', 'GN'),
    'callback_url'  => env('DJOMY_CALLBACK_URL', env('APP_URL')),
];
