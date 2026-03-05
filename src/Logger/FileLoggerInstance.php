<?php

namespace Pantono\Logger\Logger;

use Psr\Log\LoggerInterface;
use Stringable;

class FileLoggerInstance implements LoggerInterface
{

    private string $directory;
    private string $filename;
    private string $serviceName;

    public function __construct(string $directory, string $filename, string $serviceName)
    {
        $this->directory = $directory;
        $this->filename = $filename;
        $this->serviceName = $serviceName;
    }

    public function emergency(Stringable|string $message, array $context = []): void
    {
        $this->logMessage('emergency', $message, $context);
    }

    public function alert(Stringable|string $message, array $context = []): void
    {
        $this->logMessage('alert', $message, $context);
    }

    public function critical(Stringable|string $message, array $context = []): void
    {
        $this->logMessage('critical', $message, $context);
    }

    public function error(Stringable|string $message, array $context = []): void
    {
        $this->logMessage('error', $message, $context);
    }

    public function warning(Stringable|string $message, array $context = []): void
    {
        $this->logMessage('warning', $message, $context);
    }

    public function notice(Stringable|string $message, array $context = []): void
    {
        $this->logMessage('notice', $message, $context);
    }

    public function info(Stringable|string $message, array $context = []): void
    {
        $this->logMessage('info', $message, $context);
    }

    public function debug(Stringable|string $message, array $context = []): void
    {
        $this->logMessage('debug', $message, $context);
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        $this->logMessage('debug', $message, $context);
    }

    private function logMessage(string $level, string $message, array $context = []): void
    {
        if (!is_dir($this->directory)) {
            throw new \RuntimeException('Log directory does not exist: ' . $this->directory);
        }
        $file = fopen($this->directory . '/' . $this->filename, 'a');
        $message = '[' . $this->serviceName . '] [' . date('Y-m-d H:i:s') . '] ' . $level . ': ' . $message;
        if (!empty($context)) {
            $message .= ' Context:' . json_encode($context);
        }
        $message .= PHP_EOL;
        fwrite($file, $message);
        fclose($file);
    }
}
