<?php

use Shadowbane\DatadogLogger\Api\CreateDataDogApiLogger;
use Shadowbane\DatadogLogger\Api\CustomizeFormatter;

return [
    'datadog-api' => [
        'driver' => 'custom',
        'via' => CreateDataDogApiLogger::class,
        'tap' => [CustomizeFormatter::class],
        'apiKey' => env('DATADOG_API_KEY'),
        'level' => env('DATADOG_LEVEL', 'warning'),
        'bubble' => env('DATADOG_BUBBLE', true),
    ],
];
