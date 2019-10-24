<?php

declare(strict_types=1);

namespace Omikron\FactfinderNG\Plugin\Api;

use Omikron\Factfinder\Api\ClientInterface;
use Omikron\Factfinder\Api\Data\TrackingProductInterface;
use Omikron\Factfinder\Api\SessionDataInterface;
use Omikron\Factfinder\Model\Api\Tracking as TrackingModel;
use Omikron\FactfinderNG\Api\Config\NGCommunicationConfigInterface;

class Tracking
{
    /** @var NGCommunicationConfigInterface */
    private $communicationConfig;

    /** @var ClientInterface */
    private $factFinderClient;

    /** @var SessionDataInterface */
    private $sessionData;

    public function __construct(
        ClientInterface $factFinderClient,
        NGCommunicationConfigInterface $communicationConfig,
        SessionDataInterface $sessionData
    ) {
        $this->factFinderClient = $factFinderClient;
        $this->communicationConfig = $communicationConfig;
        $this->sessionData = $sessionData;
    }

    /**
     * @param TrackingModel $s
     * @param callable      $p
     * @param string        $event
     * @param array         $trackingProducts
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(TrackingModel $s, callable $p, string $event, array $trackingProducts)
    {
        $endpoint = $this->communicationConfig->getAddress()
            . sprintf('/rest/%s/track/%s/%s', $this->communicationConfig->getApi(), $this->communicationConfig->getChannel(), $event);
        $params   = array_map(function (TrackingProductInterface $trackingProduct) {
            return array_filter([
                'id'       => $trackingProduct->getTrackingNumber(),
                'masterId' => $trackingProduct->getMasterArticleNumber(),
                'price'    => $trackingProduct->getPrice(),
                'count'    => $trackingProduct->getCount(),
                'sid'      => $this->sessionData->getSessionId(),
                'userId'   => $this->sessionData->getUserId(),
            ]);
        }, $trackingProducts);

        $this->factFinderClient->sendRequest($endpoint, $params, 'post');
    }
}
