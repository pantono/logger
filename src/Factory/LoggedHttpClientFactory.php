<?php

namespace Pantono\Logger\Factory;

use Pantono\Contracts\Locator\FactoryInterface;
use Pantono\Hydrator\Locator\StaticLocator;
use Pantono\Logger\Logger;
use GuzzleHttp\Client;

class LoggedHttpClientFactory implements FactoryInterface
{
    private string $serviceName;

    public function __construct(string $serviceName)
    {
        $this->serviceName = $serviceName;
    }

    public function createInstance(): Client
    {
        /**
         * @var Logger $logger
         */
        $logger = StaticLocator::getLocator()->loadDependency('@Logger');
        return $logger->createLoggedHttpClient($this->serviceName);
    }
}
