<?php
namespace Mezon\Service\Tests;

use Mezon\Service\ServiceBaseLogicInterface;

/**
 * Tests for the class ServiceTransport
 *
 * @codeCoverageIgnore
 */
class FakeService implements ServiceBaseLogicInterface
{

    public function actionHelloWorld(): int
    {
        return 1;
    }
}
