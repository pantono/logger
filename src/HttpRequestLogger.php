<?php

namespace Pantono\Logger;

use Pantono\Logger\Repository\LoggerRepository;
use Psr\Log\LoggerInterface;
use GuzzleHttp\HandlerStack;
use Psr\Log\LogLevel;
use Pantono\Logger\Model\HttpRequestLog;

class HttpRequestLogger implements LoggerInterface
{
    private LoggerRepository $repository;
    private string $serviceName;

    public function __construct(LoggerRepository $repository, string $serviceName)
    {
        $this->repository = $repository;
        $this->serviceName = $serviceName;
    }

    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log(mixed $level, string|\Stringable $message, array $context = []): void
    {
        $pattern = '~^>{8}\R(?P<request>.*?)\R<{8}\R(?P<response>.*?)\R-{8}\R(?P<error>.*)\z~s';

        if (preg_match($pattern, $message, $m)) {
            $request = $m['request'];
            $response = $m['response'];
            $error = $m['error'];
            $log = new HttpRequestLog();
            $log->setService($this->serviceName);
            $log->setDateCompleted(new \DateTime());
            $log->setDateStarted(new \DateTime());
            $log->setResponseBody($response);
            $log->setRequestBody($request);
            $this->repository->logHttpRequest($log);
        }
    }
}
