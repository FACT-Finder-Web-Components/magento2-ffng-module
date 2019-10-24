<?php

declare(strict_types=1);

namespace Omikron\FactfinderNG\Controller\Adminhtml\TestConnection;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Omikron\Factfinder\Api\Config\AuthConfigInterface;
use Omikron\FactfinderNG\Model\Api\Credentials;
use Omikron\FactfinderNG\Model\Api\CredentialsFactory;
use Omikron\FactfinderNG\Model\Api\TestConnection as ApiConnectionTest;

class TestConnection extends Action
{
    /** @var string */
    private $obscuredValue = '******';

    /** @var JsonFactory */
    private $jsonResultFactory;

    /** @var CredentialsFactory */
    private $credentialsFactory;

    /** @var ApiConnectionTest */
    private $testConnection;

    /** @var AuthConfigInterface */
    private $authConfig;

    public function __construct(
        Action\Context $context,
        JsonFactory $jsonResultFactory,
        CredentialsFactory $credentialsFactory,
        AuthConfigInterface $authConfig,
        ApiConnectionTest $testConnection
    ) {
        parent::__construct($context);
        $this->jsonResultFactory  = $jsonResultFactory;
        $this->credentialsFactory = $credentialsFactory;
        $this->testConnection     = $testConnection;
        $this->authConfig         = $authConfig;
    }

    public function execute()
    {
        $message = __('Connection successfully established.');

        try {
            $request = $this->getRequest();
            $params  = ['channel' => $request->getParam('channel')];
            $this->testConnection->execute($request->getParam('address'), $params, $this->getCredentials($request->getParams()));
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return $this->jsonResultFactory->create()->setData(['message' => $message]);
    }

    private function getCredentials(array $params): Credentials
    {
        // The password wasn't edited, load it from config
        if (($params['password'] ?? $this->obscuredValue) === $this->obscuredValue) {
            $params['password'] = $this->authConfig->getPassword();
        }

        return $this->credentialsFactory->create($params);
    }
}
