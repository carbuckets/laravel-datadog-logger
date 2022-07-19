<?php

namespace Shadowbane\DatadogLogger\Api;

use Exception;
use Monolog\Handler\MissingExtensionException;
use Monolog\Logger;
use Shadowbane\DatadogLogger\ApiKeyNotFoundException;

/**
 * Class DataDogApiHandler
 */
class CreateDataDogApiLogger
{
    /**
     * Create the DataDog Api Logger
     *
     * @param array $config
     *
     * @return Logger
     * @throws ApiKeyNotFoundException
     * @throws MissingExtensionException
     */
    public function __invoke(array $config)
    {
        $isEuropeRegion = false;
        if (!empty($config['region']) && $config['region'] === 'eu') {
            $isEuropeRegion = true;
        }
        if (empty($config['apiKey'])) {
            throw new ApiKeyNotFoundException();
        }
        $dataDogHandler = new DataDogApiHandler(
            $config['apiKey'],
            $config['level'] ?? Logger::DEBUG,
            $config['bubble'] ?? true
        );

        return (new Logger('datadog-api'))
            ->pushHandler($dataDogHandler);
    }
}
