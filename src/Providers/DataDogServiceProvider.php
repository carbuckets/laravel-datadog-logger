<?php

namespace Shadowbane\DatadogLogger\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

/**
 * Class DataDogServiceProvider
 *
 * @extends ServiceProvider
 */
class DataDogServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/logging.php',
            'logging.channels'
        );
    }
}
