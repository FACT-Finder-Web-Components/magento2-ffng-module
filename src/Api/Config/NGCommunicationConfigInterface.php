<?php

declare(strict_types=1);

namespace Omikron\FactfinderNG\Api\Config;

interface NGCommunicationConfigInterface
{
    public function getApi(): string;

    public function getChannel(int $scopeId = null): string;

    public function getAddress(): string;
}
