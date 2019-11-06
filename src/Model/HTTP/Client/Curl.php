<?php

declare(strict_types=1);

namespace Omikron\FactfinderNG\Model\HTTP\Client;

class Curl extends \Magento\Framework\HTTP\Client\Curl
{
    protected function parseHeaders($ch, $data)
    {
        parent::parseHeaders($ch, preg_replace('#^HTTP/(.*?) ([0-9]{3})(\s*)$#', 'HTTP/$1 $2 STATUS-MESSAGE$3', $data));
        return strlen($data);
    }
}
