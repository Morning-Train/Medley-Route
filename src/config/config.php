<?php return [
    'paths' => [
        base_path('routes'),
    ],
    'query_var' => env('ROUTE_QUERY_VAR', 'medley_route'),
    'hash_option' => env('ROUTE_HASH_OPTION', 'medley_route_hash'),
    'controller_namespace' => env('ROUTE_CONTROLLER_NAMESPACE', 'MedleyApp\Controllers'),
];
