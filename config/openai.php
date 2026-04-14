<?php

return [
    'api_key' => env('OPENAI_API_KEY'),
    'base_url' => 'https://api.openai.com/v1',

    'models' => [
        'gpt-4o-2024-11-20' => 'gpt-4o-2024-11-20',
        'gpt-4o-mini-2024-07-18' => 'gpt-4o-mini-2024-07-18',
        'gpt-4o' => 'gpt-4o',
        'gpt-4o-mini' => 'gpt-4o-mini',
        'gpt-4-turbo-2024-04-09' => 'gpt-4-turbo-2024-04-09',
        'gpt-4-0613' => 'gpt-4-0613',
        'gpt-3.5-turbo-0125' => 'gpt-3.5-turbo-0125',
    ],

    'default_model' => 'gpt-4o-mini',
];
