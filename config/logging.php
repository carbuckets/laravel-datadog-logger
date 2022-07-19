<?php

use Shadowbane\DatadogLogger\Api\CreateDataDogApiLogger;

return [
    'datadog-api'   => [
        'driver' => 'custom',
        'via'    => CreateDataDogApiLogger::class,
        'apiKey' => env('DATADOG_API_KEY'),
        'region' => env('DATADOG_REGION', 'eu'),   // eu or us
        'level'  => env('DATADOG_LEVEL', 'info'),  // choose your minimum level of logging.
        'bubble' => env('DATADOG_BUBBLE', true),
    ],
];
