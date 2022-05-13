<?php
namespace Mezon\Service\Tests;

use Mezon\Service\ServiceActionsInterface;

/**
 * Tests for the class ServiceTransport
 *
 * @codeCoverageIgnore
 */
class FakeService implements ServiceActionsInterface
{

    public function actionHelloWorld(): int
    {
        return 1;
    }
}
