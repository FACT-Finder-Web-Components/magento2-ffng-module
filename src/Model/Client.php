<?php

declare(strict_types=1);

namespace Omikron\FactfinderNG\Model;

use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Omikron\Factfinder\Api\ClientInterface as ApiClientInterface;
use Omikron\Factfinder\Api\Config\AuthConfigInterface;
use Omikron\Factfinder\Exception\ResponseException;
use Omikron\FactfinderNG\Model\Api\Credentials;
use Omikron\FactfinderNG\Model\Api\CredentialsFactory;

class Client implements ApiClientInterface
{
    /** @var ClientFactory */
    private $httpClientFactory;

    /** @var SerializerInterface */
    private $serializer;

    /** @var AuthConfigInterface */
    private $authConfig;

    /** @var CredentialsFactory */
    private $credentialsFactory;

    /** @var Credentials */
    private $credentials;

    public function __construct(
        ClientFactory $clientFactory,
        SerializerInterface $serializer,
        AuthConfigInterface $authConfig,
        CredentialsFactory $credentialsFactory,
        Credentials $credentials = null
    ) {
        $this->httpClientFactory  = $clientFactory;
        $this->serializer         = $serializer;
        $this->authConfig         = $authConfig;
        $this->credentialsFactory = $credentialsFactory;
        $this->credentials        = $credentials;
    }

    public function sendRequest(string $endpoint, array $params, string $method = 'get'): array
    {
        $httpClient = $this->httpClientFactory->create();

        try {
            $httpClient->addHeader('Accept', 'application/json');
            $httpClient->addHeader('Authorization', $this->credentials ?? $this->getAuth($this->authConfig));

            if ($method == 'get') {
                $query = preg_replace('#products%5B\d+%5D%5B(.+?)%5D=#', '\1=', http_build_query($params));
                $httpClient->get($endpoint . '?' . $query);
            } else {
                $httpClient->addHeader('Content-Type', 'application/json');
                $httpClient->post($endpoint, $this->serializer->serialize($params));
            }

            if ($httpClient->getStatus() == 200) {
                if ($httpClient->getBody()) {
                    return (array) $this->serializer->unserialize($httpClient->getBody());
                } else {
                    return $httpClient->getHeaders();
                }
            }

            throw new ResponseException($httpClient->getBody(), $httpClient->getStatus());
        } catch (\InvalidArgumentException $e) {
            throw new ResponseException($httpClient->getBody(), $httpClient->getStatus(), $e);
        }
    }

    private function getAuth(AuthConfigInterface $config): Credentials
    {
        return $this->credentialsFactory->create([
            'username' => $config->getUsername(),
            'password' => $config->getPassword(),
        ]);
    }
}
