<?php declare(strict_types=1);

return [
    'function'     => [
        'name'    => env('AWS_LAMBDA_FUNCTION_NAME', ''),
        'version' => env('AWS_LAMBDA_FUNCTION_VERSION', ''),
    ],
    'logging'      => [
        'group'  => env('AWS_LAMBDA_LOG_GROUP_NAME', ''),
        'stream' => env('AWS_LAMBDA_LOG_STREAM_NAME', ''),
    ],
    'memory_limit' => env('AWS_LAMBDA_FUNCTION_MEMORY_SIZE', ''),
    
];