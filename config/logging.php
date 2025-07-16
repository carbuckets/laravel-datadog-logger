<?php

use Shadowbane\DatadogLogger\Api\CreateDataDogApiLogger;
use Shadowbane\DatadogLogger\Api\CustomizeFormatter;

return [
    'datadog-api' => [
        'driver' => 'custom',
        'via' => CreateDataDogApiLogger::class,
        'tap' => [CustomizeFormatter::class],
        'endpoint' => env('DATADOG_API_ENDPOINT', 'https://http-intake.logs.datadoghq.com/api/v2/logs'),
        'apiKey' => env('DATADOG_API_KEY', env('DD_API_KEY')),
        'level' => env('DATADOG_LEVEL', env('LOG_LEVEL', 'warning')),
        'bubble' => env('DATADOG_BUBBLE', true),

        /*
        |--------------------------------------------------------------------------
        | Error Reporting
        |--------------------------------------------------------------------------
        |
        | If this package encountered issue while sending the API request to the
        | DataDog API, logs the error to specified channel.
        |
        */
        'error' => env('DATADOG_ERROR_LOG_CHANNEL', false),
    ],
];
