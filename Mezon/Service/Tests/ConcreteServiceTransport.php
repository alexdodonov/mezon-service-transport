<?php
namespace Mezon\Service\Tests;

use Mezon\Service\Transport;
use Mezon\Transport\RequestParamsInterface;

class ConcreteServiceTransport extends Transport
{
    
    public function createFetcher(): RequestParamsInterface
    {
        return new ConcreteFetcher();
    }
    
    public function createSession(string $token): string
    {
        return $token;
    }
}
