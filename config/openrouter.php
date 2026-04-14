<?php

return [
    'api_key' => env('OPENROUTER_API_KEY'),
    'base_url' => 'https://openrouter.ai/api/v1',

    'models' => [
        'anthropic/claude-3.5-sonnet' => 'anthropic/claude-3.5-sonnet',
        'anthropic/claude-3.5-haiku' => 'anthropic/claude-3.5-haiku',
        'anthropic/claude-3-opus' => 'anthropic/claude-3-opus',
        'anthropic/claude-3-sonnet' => 'anthropic/claude-3-sonnet',
        'anthropic/claude-3-haiku' => 'anthropic/claude-3-haiku',
        'openai/gpt-4o' => 'openai/gpt-4o',
        'openai/gpt-4o-mini' => 'openai/gpt-4o-mini',
        'openai/gpt-4-turbo' => 'openai/gpt-4-turbo',
        'google/gemini-2.0-flash-exp' => 'google/gemini-2.0-flash-exp',
        'meta-llama/llama-3-70b-instruct' => 'meta-llama/llama-3-70b-instruct',
        'mistralai/mistral-large' => 'mistralai/mistral-large',
        'cohere/command-r-plus' => 'cohere/command-r-plus',
    ],

    'default_model' => 'anthropic/claude-3.5-sonnet',
];
