<?php

namespace Pantono\Logger;

use Pantono\Logger\Repository\LoggerRepository;
use Psr\Log\LoggerInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;

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
        $psrLog = new HttpRequestLogger($this->repository, $serviceName);

        $stack = HandlerStack::create();
        $stack->push(Middleware::log($psrLog, new MessageFormatter(MessageFormatter::DEBUG)));

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
