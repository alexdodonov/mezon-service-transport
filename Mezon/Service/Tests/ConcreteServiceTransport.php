<?php
namespace Mezon\Service\Tests;

use Mezon\Service\Transport;
use Mezon\Transport\RequestParamsInterface;

class ConcreteServiceTransport extends Transport
{
    
    protected function createFetcher(): RequestParamsInterface
    {
        return new ConcreteFetcher();
    }
}
