<?php

declare(strict_types=1);

namespace Omikron\FactfinderNG\Plugin\Api;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Omikron\Factfinder\Api\ClientInterface;
use Omikron\Factfinder\Model\Api\PushImport as PushImportModel;
use Omikron\FactfinderNG\Api\Config\NGCommunicationConfigInterface;

class PushImport
{
    /** @var ClientInterface */
    protected $apiClient;

    /** @var NGCommunicationConfigInterface */
    protected $communicationConfig;

    /** @var string */
    protected $apiName = 'Import.ff';

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    public function __construct(
        ClientInterface $apiClient,
        NGCommunicationConfigInterface $communicationConfig,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->apiClient           = $apiClient;
        $this->communicationConfig = $communicationConfig;
        $this->scopeConfig         = $scopeConfig;
    }

    /**
     * @param PushImportModel $s
     * @param callable        $p
     * @param int             $scopeId
     * @param array           $params
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(PushImportModel $s, callable $p, int $scopeId = null, array $params = [])
    {
        if (!$this->scopeConfig->isSetFlag('factfinder/data_transfer/ff_push_import_enabled', ScopeInterface::SCOPE_STORES, $scopeId)) {
            return false;
        }

        $params += [
            'channel'  => $this->communicationConfig->getChannel($scopeId),
            'quiet'    => 'true',
        ];

        $response = [];
        $endpoint = $this->communicationConfig->getAddress() . sprintf('/rest/%s/import', $this->communicationConfig->getApi());
        foreach ($this->getPushImportDataTypes($scopeId) as $type) {
            $response = array_merge_recursive($response, $this->apiClient->sendRequest($endpoint . "/$type", $params, 'post'));
        }

        return $response && !(isset($response['errors']) || isset($response['error']));
    }

    private function getPushImportDataTypes(int $scopeId = null): array
    {
        $configPath = 'factfinder/data_transfer/ff_push_import_type';
        $dataTypes  = (string) $this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE, $scopeId);
        return $dataTypes ? explode(',', $dataTypes) : [];
    }
}
