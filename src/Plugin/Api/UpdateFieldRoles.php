<?php

declare(strict_types=1);

namespace Omikron\FactfinderNG\Plugin\Api;

use Omikron\Factfinder\Api\ClientInterface;
use Omikron\Factfinder\Api\FieldRolesInterface;
use Omikron\Factfinder\Exception\ResponseException;
use Omikron\Factfinder\Model\Api\UpdateFieldRoles as UpdateFieldRolesModel;
use Omikron\FactfinderNG\Api\Config\NGCommunicationConfigInterface;

class UpdateFieldRoles
{
    /** @var FieldRolesInterface */
    private $fieldRoles;

    /** @var ClientInterface */
    private $factFinderClient;

    /** @var CommunicationConfigInterface */
    private $communicationConfig;

    /** @var string */
    private $apiQuery = 'FACT-Finder version';

    public function __construct(
        FieldRolesInterface $fieldRoles,
        ClientInterface $factFinderClient,
        NGCommunicationConfigInterface $communicationConfig
    ) {
        $this->fieldRoles          = $fieldRoles;
        $this->factFinderClient    = $factFinderClient;
        $this->communicationConfig = $communicationConfig;
    }

    /**
     * @param UpdateFieldRolesModel $s
     * @param callable              $p
     * @param int|null              $scopeId
     * @param array                 $params
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(UpdateFieldRolesModel $s, callable $p, int $scopeId = null, array $params = [])
    {
        $endpoint =  ($params['serverUrl'] ?? $this->communicationConfig->getAddress())
             . sprintf('/rest/%s/search/%s', $this->communicationConfig->getApi(), $this->communicationConfig->getChannel());
        $response = $this->factFinderClient->sendRequest($endpoint, $params + ['query' => $this->apiQuery]);

        if ($response['fieldRoles'] ?? []) {
            $this->fieldRoles->saveFieldRoles($response['fieldRoles'], $scopeId);
            return true;
        }

        throw new ResponseException('FACT-Finder Response does not contain field roles information');
    }
}
