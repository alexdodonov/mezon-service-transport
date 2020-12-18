<?php
namespace Mezon\Service\Tests;

use Mezon\Transport\RequestParamsInterface;

class ConcreteFetcher implements RequestParamsInterface
{

    public function getParam($param, $default = false)
    {
        return 1;
    }

    public function wasSubmitted(string $param): bool
    {
        return false;
    }
}
