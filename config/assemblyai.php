<?php

return [
    'api_key' => env('ASSEMBLYAI_API_KEY'),
    'base_url' => 'https://api.assemblyai.com',
    'streaming_url' => 'wss://streaming.assemblyai.com',

    'endpoints' => [
        'transcript' => '/v2/transcript',
        'upload' => '/v2/upload',
        'subtitles' => '/v2/transcript/{id}/subtitles',
        'words' => '/v2/transcript/{id}/words',
    ],

    'models' => [
        'universal_3_pro' => 'universal-3-pro',
        'universal_2' => 'universal-2',
    ],
];
