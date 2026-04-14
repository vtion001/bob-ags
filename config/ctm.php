<?php

return [
    'host' => env('CTM_API_HOST', 'api.calltrackingmetrics.com'),
    'access_key' => env('CTM_ACCESS_KEY'),
    'secret_key' => env('CTM_SECRET_KEY'),
    'account_id' => env('CTM_ACCOUNT_ID'),

    'base_url' => 'https://api.calltrackingmetrics.com/api/v1',

    'endpoints' => [
        'accounts' => '/accounts',
        'calls' => '/calls',
        'numbers' => '/numbers',
        'sources' => '/sources',
        'receiving_numbers' => '/receiving_numbers',
        'voice_menus' => '/voice_menus',
        'schedules' => '/schedules',
    ],
];
