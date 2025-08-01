<?php

namespace Shadowbane\DatadogLogger\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\MissingExtensionException;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;

/**
 * Class DataDogApiHandler.
 *
 * @extends AbstractProcessingHandler
 */
class DataDogApiHandler extends AbstractProcessingHandler
{
    /** @var string */
    protected string $token;

    /**
     * @param string $token API token supplied by DataDog
     * @param string|int $level The minimum logging level to trigger this handler
     * @param bool $bubble whether or not messages that are handled should bubble up the stack
     *
     * @throws MissingExtensionException If the curl extension is missing
     */
    public function __construct(string $token, string|int $level = Logger::WARNING, bool $bubble = true)
    {
        if (!extension_loaded('curl')) {
            throw new MissingExtensionException('The curl extension is needed to use the DataDogApiHandler');
        }
        $this->token = $token;
        parent::__construct($level, $bubble);
    }

    /**
     * Get endpoint used to send the logs.
     *
     * @return string
     */
    protected function getEndpoint(): string
    {
        return config('logging.channels.datadog-api.endpoint', 'https://http-intake.logs.datadoghq.com/api/v2/logs');
    }

    /**
     * Write implementation of AbstractProcessingHandler.
     *
     * @param LogRecord $record
     *
     * @return void
     */
    protected function write(LogRecord $record): void
    {
        $this->send($record);
    }

    /**
     * Send the log.
     *
     * @param LogRecord $record
     *
     * @return void
     */
    protected function send(LogRecord $record): void
    {
        try {
            $client = new Client();
            $client->request(
                'POST',
                $this->getEndpoint(),
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'DD-API-KEY' => $this->token,
                        'Accept-Encoding' => 'gzip',
                    ],
                    'body' => json_encode($this->createBody($record)),
                ]
            );
        } catch (GuzzleException $e) {
            $errLoggingChannel = config('logging.channels.datadog-api.error');
            if ($errLoggingChannel) {
                Log::channel($errLoggingChannel)->critical($e->getMessage(), [
                    'exception' => $e,
                ]);
            }

            return;
        }
    }

    /**
     * Create the body of the log to send
     * to DataDog via the API.
     *
     * @param LogRecord $record
     *
     * @return array
     */
    private function createBody(LogRecord $record): array
    {
        $hostname = parse_url(config('app.url'), PHP_URL_HOST) ?: gethostname();

        $body = [
            'ddsource' => 'laravel',
            'ddtags' => $this->getTags(),
            'hostname' => $hostname,
            'message' => $record->formatted,
            'context' => $record->context,
            'service' => env('DD_SERVICE', config('app.name')),
            'status' => $this->getLogStatus($record->level),
            'timestamp' => now()->getPreciseTimestamp(3),
        ];

        if (!blank($record->context) && isset($record->context['tenant'])) {
            $body['weelz.tenant'] = $record->context['tenant'];
        }

        if (!blank($record->context) && isset($record->context['exception']) && $record->context['exception'] instanceof \Exception) {
            /** @var \Exception $exception */
            $exception = $record->context['exception'];
            $body['error.kind'] = $exception->getCode();
            $body['error.message'] = $exception->getMessage();
            $body['error.stack'] = $exception->getTraceAsString();

            // replace message with exception class
            $body['context'] = Arr::except($record->context, ['exception']);
            $body['context']['message_ref'] = $record->message;
            $body['message'] = get_class($exception);
        }

        return $body;
    }

    /**
     * Returns string of tags.
     * The string by default will send current environment.
     * To override this, you can use DATADOG_ENVIRONMENT
     * on you .env file.
     *
     * @return string
     */
    private function getTags(): string
    {
        $envString = env('DATADOG_ENVIRONMENT', app()->environment());

        return 'env:'.$envString;
    }

    /**
     * Translate Laravel error to DataDog error.
     *
     * @param Level $status
     *
     * @return string
     */
    private function getLogStatus(Level $status): string
    {
        // convert to lowercase to prevent error
        $statusString = strtolower($status->getName());

        if (in_array($statusString, ['debug', 'info'])) {
            return 'info';
        }

        if (in_array($statusString, ['notice', 'warning'])) {
            return 'warn';
        }

        return 'error';
    }
}
