<?php

namespace Shadowbane\DatadogLogger;

use Exception;

/**
 * Class ApiKeyNotFoundException
 *
 * @extends Exception
 */
class ApiKeyNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct('No API Key Provided', 401, null);
    }
}
