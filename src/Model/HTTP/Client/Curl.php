<?php

declare(strict_types=1);

namespace Omikron\FactfinderNG\Model\HTTP\Client;

class Curl extends \Magento\Framework\HTTP\Client\Curl
{
    protected function parseHeaders($ch, $data)
    {
        parent::parseHeaders($ch, preg_replace('# ([0-9]{3})(\s*)$#', ' $1 STATUS-MESSAGE$2', $data));
        return strlen($data);
    }
}
