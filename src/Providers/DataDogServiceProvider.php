<?php

namespace Shadowbane\DatadogLogger\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class DataDogServiceProvider.
 *
 * @extends ServiceProvider
 */
class DataDogServiceProvider extends ServiceProvider
{
    /**
     * Register laravel-datadog-logger,
     * merge configuration into the logging.channels array.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/logging.php',
            'logging.channels'
        );
    }
}
