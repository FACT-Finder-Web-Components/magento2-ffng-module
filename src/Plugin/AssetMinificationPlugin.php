<?php

declare(strict_types=1);

namespace Omikron\FactfinderNG\Plugin;

class AssetMinificationPlugin
{
    public function afterGetExcludes($subject, array $result, string $contentType): array
    {
        return array_merge($result, $contentType === 'js' ? ['/Omikron_FactfinderNG/ff-web-components/'] : []);
    }
}
