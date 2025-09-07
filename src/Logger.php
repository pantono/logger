<?php

namespace Pantono\Logger;

use Pantono\Logger\Repository\LoggerRepository;
use Psr\Log\LoggerInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Promise\PromiseInterface;
use Pantono\Logger\Model\HttpRequestLog;

class Logger implements LoggerInterface
{
    private LoggerRepository $repository;

    public function __construct(LoggerRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createLogger(string $serviceName): LoggerInstance
    {
        return new LoggerInstance($this->repository, $serviceName);
    }

    public function createLoggedHttpClient(string $serviceName): Client
    {
        $stack = HandlerStack::create();
        $stack->push(Middleware::tap(function (Request $request, array &$options) {
            $options['request_time_start'] = microtime(true);
            $options['request_datetime_start'] = new \DateTime();
        }, function (Request $request, array $options, PromiseInterface $response) use ($serviceName) {
            $response->then(function ($response) use ($request, $options, $serviceName) {
                $requestLog = new HttpRequestLog();
                $requestLog->setService($serviceName);
                $requestLog->setDateStarted($options['request_datetime_start']);
                $requestLog->setDateCompleted(new \DateTime());
                if ($request->getBody()->getSize() > 0) {
                    $requestLog->setRequestBody((string)$request->getBody());
                }
                $requestLog->setUri($request->getUri());
                $requestLog->setMethod($request->getMethod());
                $requestLog->setRequestHeaders($request->getHeaders());
                $requestLog->setResponseBody((string)$response->getBody());
                $requestLog->setResponseCode($response->getStatusCode());
                $requestLog->setResponseHeaders($response->getHeaders());
                $requestLog->setTimeTaken(microtime(true) - $options['request_time_start']);
                $this->repository->logHttpRequest($requestLog);
            });
        }));

        return new Client(['handler' => $stack]);
    }

    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->doLog('emergency', $message, $context);
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->doLog('alert', $message, $context);
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->doLog('critical', $message, $context);
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->doLog('error', $message, $context);
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->doLog('warning', $message, $context);
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->doLog('notice', $message, $context);
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->doLog('info', $message, $context);
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->doLog('debug', $message, $context);
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->doLog($level, $message, $context);
    }


    private function doLog(string $level, string $message, array $context = []): void
    {
        $this->repository->logMessage('application', $level, $message, $context);
    }
}
