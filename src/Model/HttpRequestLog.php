<?php

namespace Pantono\Logger\Model;

use Pantono\Contracts\Attributes\Filter;
use Pantono\Database\Traits\SavableModel;

class HttpRequestLog
{
    use SavableModel;

    private ?int $id = null;
    private \DateTimeInterface $dateStarted;
    private ?\DateTimeInterface $dateCompleted = null;
    private ?string $service = null;
    private ?string $method = null;
    private ?string $uri = null;
    /** @var array<string, string|string[]> */
    #[Filter('json_decode')]
    private array $requestHeaders = [];
    private ?string $requestBody = null;
    private int $responseCode;
    /** @var array<string, string|string[]> */
    #[Filter('json_decode')]
    private array $responseHeaders = [];
    private ?string $responseBody = null;
    private ?float $timeTaken = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getDateStarted(): \DateTimeInterface
    {
        return $this->dateStarted;
    }

    public function setDateStarted(\DateTimeInterface $dateStarted): void
    {
        $this->dateStarted = $dateStarted;
    }

    public function getDateCompleted(): ?\DateTimeInterface
    {
        return $this->dateCompleted;
    }

    public function setDateCompleted(?\DateTimeInterface $dateCompleted): void
    {
        $this->dateCompleted = $dateCompleted;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(?string $service): void
    {
        $this->service = $service;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): void
    {
        $this->method = $method;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(?string $uri): void
    {
        $this->uri = $uri;
    }

    public function getRequestHeaders(): array
    {
        return $this->requestHeaders;
    }

    public function setRequestHeaders(array $requestHeaders): void
    {
        $this->requestHeaders = $requestHeaders;
    }

    public function getRequestBody(): ?string
    {
        return $this->requestBody;
    }

    public function setRequestBody(?string $requestBody): void
    {
        $this->requestBody = $requestBody;
    }

    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    public function setResponseCode(int $responseCode): void
    {
        $this->responseCode = $responseCode;
    }

    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }

    public function setResponseHeaders(array $responseHeaders): void
    {
        $this->responseHeaders = $responseHeaders;
    }

    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    public function setResponseBody(?string $responseBody): void
    {
        $this->responseBody = $responseBody;
    }

    public function getTimeTaken(): ?float
    {
        return $this->timeTaken;
    }

    public function setTimeTaken(?float $timeTaken): void
    {
        $this->timeTaken = $timeTaken;
    }
}
