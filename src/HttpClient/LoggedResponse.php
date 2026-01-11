<?php

namespace Pantono\Logger\HttpClient;

use Symfony\Contracts\HttpClient\ResponseInterface;
use Pantono\Logger\Repository\LoggerRepository;
use Pantono\Logger\Model\HttpRequestLog;

class LoggedResponse implements ResponseInterface
{
    private ResponseInterface $response;
    private LoggerRepository $repository;
    private string $serviceName;
    private string $method;
    private string $url;
    private array $options;
    private float $startTime;
    private \DateTimeInterface $startDate;
    private bool $logged = false;

    public function __construct(
        ResponseInterface  $response,
        LoggerRepository   $repository,
        string             $serviceName,
        string             $method,
        string             $url,
        array              $options,
        float              $startTime,
        \DateTimeInterface $startDate
    )
    {
        $this->response = $response;
        $this->repository = $repository;
        $this->serviceName = $serviceName;
        $this->method = $method;
        $this->url = $url;
        $this->options = $options;
        $this->startTime = $startTime;
        $this->startDate = $startDate;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getStatusCode(): int
    {
        $statusCode = $this->response->getStatusCode();
        $this->log();
        return $statusCode;
    }

    public function getHeaders(bool $throw = true): array
    {
        $headers = $this->response->getHeaders($throw);
        $this->log();
        return $headers;
    }

    public function getContent(bool $throw = true): string
    {
        $content = $this->response->getContent($throw);
        $this->log();
        return $content;
    }

    public function toArray(bool $throw = true): array
    {
        $array = $this->response->toArray($throw);
        $this->log();
        return $array;
    }

    public function cancel(): void
    {
        $this->response->cancel();
    }

    public function getInfo(?string $type = null): mixed
    {
        return $this->response->getInfo($type);
    }

    public function __destruct()
    {
        $this->log();
    }

    private function log(): void
    {
        if ($this->logged) {
            return;
        }
        $this->logged = true;
        $requestLog = new HttpRequestLog();
        $requestLog->setService($this->serviceName);
        $requestLog->setDateStarted($this->startDate);
        $requestLog->setDateCompleted(new \DateTime());
        $requestLog->setMethod($this->method);
        $requestLog->setUri($this->url);

        $requestHeaders = $this->options['headers'] ?? [];
        $requestLog->setRequestHeaders($requestHeaders);

        if (isset($this->options['body'])) {
            $requestLog->setRequestBody((string)$this->options['body']);
        } elseif (isset($this->options['json'])) {
            $requestLog->setRequestBody(json_encode($this->options['json']));
        }

        try {
            $requestLog->setResponseCode($this->response->getStatusCode());
            $requestLog->setResponseHeaders($this->response->getHeaders(false));
            $requestLog->setResponseBody($this->response->getContent(false));
        } catch (\Exception $e) {
            // Log might be incomplete if response fails
        }

        $requestLog->setTimeTaken(microtime(true) - $this->startTime);
        $this->repository->logHttpRequest($requestLog);
    }
}
