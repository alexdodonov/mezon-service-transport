<?php
namespace Mezon\Service\Tests;

use Mezon\Security\ProviderInterface;

/**
 * Fake security provider
 *
 * @codeCoverageIgnore
 */
class FakeProvider implements ProviderInterface
{

    public function createSession(string $token): string
    {
        return $token;
    }
}
