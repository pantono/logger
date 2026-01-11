<?php

namespace Pantono\Logger\HttpClient;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Pantono\Logger\Repository\LoggerRepository;
use Pantono\Logger\Model\HttpRequestLog;

class LoggedHttpClient implements HttpClientInterface
{
    private HttpClientInterface $client;
    private LoggerRepository $repository;
    private string $serviceName;

    public function __construct(HttpClientInterface $client, LoggerRepository $repository, string $serviceName)
    {
        $this->client = $client;
        $this->repository = $repository;
        $this->serviceName = $serviceName;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $startTime = microtime(true);
        $startDate = new \DateTime();
        $response = $this->client->request($method, $url, $options);

        return new LoggedResponse($response, $this->repository, $this->serviceName, $method, $url, $options, $startTime, $startDate);
    }

    public function stream(ResponseInterface|iterable $responses, ?float $timeout = null): ResponseStreamInterface
    {
        if ($responses instanceof LoggedResponse) {
            $responses = $responses->getResponse();
        } elseif (is_iterable($responses)) {
            $unwrappedResponses = function () use ($responses) {
                foreach ($responses as $key => $response) {
                    if ($response instanceof LoggedResponse) {
                        yield $key => $response->getResponse();
                    } else {
                        yield $key => $response;
                    }
                }
            };
            $responses = $unwrappedResponses();
        }
        return $this->client->stream($responses, $timeout);
    }

    public function withOptions(array $options): static
    {
        return new static($this->client->withOptions($options), $this->repository, $this->serviceName);
    }
}
