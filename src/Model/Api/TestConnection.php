<?php

declare(strict_types=1);

namespace Omikron\FactfinderNG\Model\Api;

use Omikron\Factfinder\Exception\ResponseException;
use Omikron\FactfinderNG\Api\Config\NGCommunicationConfigInterface;
use Omikron\FactfinderNG\Model\ClientFactory;

class TestConnection
{
    /** @var ClientFactory */
    private $apiClientFactory;

    /** @var NGCommunicationConfigInterface */
    private $communicationConfig;

    /** @var string */
    private $apiQuery = 'FACT-Finder version';

    public function __construct(ClientFactory $clientFactory, NGCommunicationConfigInterface $communicationConfig)
    {
        $this->apiClientFactory    = $clientFactory;
        $this->communicationConfig = $communicationConfig;
    }

    /**
     * @param string $serverUrl
     * @param array  $params
     *
     * @return bool
     * @throws ResponseException
     */
    public function execute(string $serverUrl, array $params, Credentials $credentials): bool
    {
        $endpoint = rtrim($serverUrl, '/') . sprintf('/rest/%s/search/%s', $this->communicationConfig->getApi(), $this->communicationConfig->getChannel());
        $client = $this->apiClientFactory->create(['credentials' => $credentials]);
        $client->sendRequest($endpoint, $params + ['query' => $this->apiQuery]);
        return true;
    }
}
