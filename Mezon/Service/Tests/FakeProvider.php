<?php
namespace Mezon\Service\Tests;

use Mezon\Security\ProviderInterface;

class FakeProvider implements ProviderInterface
{

    public function createSession(string $token): string
    {
        return $token;
    }
}
