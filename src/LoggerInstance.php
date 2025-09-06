<?php

namespace Pantono\Logger;

use Psr\Log\LoggerInterface;
use Pantono\Logger\Repository\LoggerRepository;
use Stringable;

class LoggerInstance implements LoggerInterface
{
    private LoggerRepository $repository;
    private string $serviceName;

    public function __construct(LoggerRepository $repository, string $serviceName)
    {
        $this->repository = $repository;
        $this->serviceName = $serviceName;
    }

    public function emergency(Stringable|string $message, array $context = []): void
    {
        $this->repository->logMessage($this->serviceName, 'emergency', $message, $context);
    }

    public function alert(Stringable|string $message, array $context = []): void
    {
        $this->repository->logMessage($this->serviceName, 'alert', $message, $context);
    }

    public function critical(Stringable|string $message, array $context = []): void
    {
        $this->repository->logMessage($this->serviceName, 'critical', $message, $context);
    }

    public function error(Stringable|string $message, array $context = []): void
    {
        $this->repository->logMessage($this->serviceName, 'error', $message, $context);
    }

    public function warning(Stringable|string $message, array $context = []): void
    {
        $this->repository->logMessage($this->serviceName, 'warning', $message, $context);
    }

    public function notice(Stringable|string $message, array $context = []): void
    {
        $this->repository->logMessage($this->serviceName, 'notice', $message, $context);
    }

    public function info(Stringable|string $message, array $context = []): void
    {
        $this->repository->logMessage($this->serviceName, 'info', $message, $context);
    }

    public function debug(Stringable|string $message, array $context = []): void
    {
        // TODO: Implement debug() method.
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        // TODO: Implement log() method.
    }


}
