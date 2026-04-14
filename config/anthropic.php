<?php

return [
    'api_key' => env('ANTHROPIC_API_KEY'),
    'base_url' => 'https://api.anthropic.com/v1',
    'api_version' => '2023-06-01',

    'models' => [
        'claude-3-5-sonnet-20241022' => 'claude-3-5-sonnet-20241022',
        'claude-3-5-sonnet-20240620' => 'claude-3-5-sonnet-20240620',
        'claude-3-5-haiku-20241022' => 'claude-3-5-haiku-20241022',
        'claude-3-opus-20240229' => 'claude-3-opus-20240229',
        'claude-3-sonnet-20240229' => 'claude-3-sonnet-20240229',
        'claude-3-haiku-20240307' => 'claude-3-haiku-20240307',
    ],

    'default_model' => 'claude-3-5-sonnet-20241022',
];
