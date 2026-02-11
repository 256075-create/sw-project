<?php

return [
    'jwt' => [
        'secret' => env('JWT_SECRET', 'your-jwt-secret-key'),
        'algo' => env('JWT_ALGO', 'HS256'),
        'access_token_ttl' => env('JWT_ACCESS_TTL', 15), // 15 minutes
        'refresh_token_ttl' => env('JWT_REFRESH_TTL', 1440), // 24 hours
        'issuer' => env('JWT_ISSUER', 'ums-api'),
        'audience' => env('JWT_AUDIENCE', 'ums-client'),
    ],

    'elasticsearch' => [
        'host' => env('ELASTICSEARCH_HOST', 'localhost:9200'),
    ],
];
