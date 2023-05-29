<?php

namespace Shadowbane\DatadogLogger\Commands;

use Illuminate\Console\Command;
use Throwable;

class TestException extends \Exception
{
    /**
     * @param string  $message
     * @param int  $code
     * @param Throwable|null  $previous
     */
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class SendException extends Command
{
    protected $name = 'datadog:send-test-exception';

    protected $description = 'Test sending exception to datadog logger via API';

    /**
     * Execute the console command.
     *
     * @throws TestException
     */
    public function handle() : int
    {
        throw new TestException('This is test exception. Sent at: '.date('Y-m-d H:i:s'), 500);
    }
}
