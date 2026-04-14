<?php

return [
    'api_key' => env('OPENROUTER_API_KEY'),
    'base_url' => 'https://openrouter.ai/api/v1',

    'models' => [
        'claude_haiku' => 'anthropic/claude-3-haiku',
        'claude_sonnet' => 'anthropic/claude-3.5-sonnet',
    ],

    'default_model' => 'anthropic/claude-3-haiku',
];
