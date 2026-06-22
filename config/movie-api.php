<?php

return [
    'providers' => [
        'omdb' => [
            'api_key'  => env('OMDB_API_KEY'),
            'base_url' => 'https://www.omdbapi.com/',
        ],
    ],
];
