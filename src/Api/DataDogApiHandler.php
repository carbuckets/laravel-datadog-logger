<?php

namespace Shadowbane\DatadogLogger\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\MissingExtensionException;
use Monolog\Logger;

/**
 * Class DataDogApiHandler.
 *
 * @extends AbstractProcessingHandler
 */
class DataDogApiHandler extends AbstractProcessingHandler
{
    /** @var string */
    protected string $token;

    /** @var string */
    protected static string $ENDPOINT = 'https://http-intake.logs.datadoghq.com/api/v2/logs';

    /**
     * @param string $token API token supplied by DataDog
     * @param string|int $level The minimum logging level to trigger this handler
     * @param bool $bubble whether or not messages that are handled should bubble up the stack
     *
     * @throws MissingExtensionException If the curl extension is missing
     */
    public function __construct(string $token, $level = Logger::WARNING, bool $bubble = true)
    {
        if (!extension_loaded('curl')) {
            throw new MissingExtensionException('The curl extension is needed to use the DataDogApiHandler');
        }
        $this->token = $token;
        parent::__construct($level, $bubble);
    }

    /**
     * Write implementation of AbstractProcessingHandler.
     *
     * @param array $record
     *
     * @return void
     */
    protected function write(array $record): void
    {
        $this->send($record);
    }

    /**
     * Send the log.
     *
     * @param array $record
     *
     * @return void
     */
    protected function send(array $record): void
    {
        try {
            $client = new Client();
            $client->request(
                'POST',
                self::$ENDPOINT,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'DD-API-KEY' => $this->token,
                    ],
                    'body' => json_encode($this->createBody($record)),
                ]
            );
        } catch (GuzzleException $e) {
            return;
        }
    }

    /**
     * Create the body of the log to send
     * to DataDog via the API.
     *
     * @param array $record
     *
     * @return array
     */
    private function createBody(array $record): array
    {
        $body = [
            'ddsource' => 'laravel',
            'ddtags' => 'env:'.app()->environment(),
            'hostname' => gethostname(),
            'message' => $record['formatted'],
            'service' => config('app.name'),
            'status' => $this->getLogStatus($record['level_name']),
        ];

        if (!blank($record['context']) && $record['context']['exception'] instanceof \Exception) {
            /** @var \Exception $exception */
            $exception = $record['context']['exception'];
            $body['error.kind'] = $exception->getCode();
            $body['error.message'] = $exception->getMessage();
            $body['error.stack'] = $exception->getTraceAsString();

            // replace message with exception class
            $body['message'] = get_class($exception);
        }

        return $body;
    }

    /**
     * Translate Laravel error to DataDog error.
     *
     * @param string $status
     *
     * @return string
     */
    private function getLogStatus(string $status): string
    {
        // convert to lowercase to prevent error
        $status = strtolower($status);

        if (in_array($status, ['debug', 'info'])) {
            return 'info';
        }

        if (in_array($status, ['notice', 'warning'])) {
            return 'warn';
        }

        return 'error';
    }
}
