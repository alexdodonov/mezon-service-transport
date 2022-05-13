<?php
namespace Mezon\Service\Tests;

use Mezon\Service\ServiceBaseLogic;
use Mezon\Transport\Tests\MockParamsFetcher;
use Mezon\Security\MockProvider;

/**
 * Tests for the class ServiceTransport
 *
 * @codeCoverageIgnore
 */
class FakeService extends ServiceBaseLogic
{

    public function __construct()
    {
        parent::__construct(new MockParamsFetcher(), new MockProvider());
    }

    public function actionHelloWorld(): int
    {
        return 1;
    }
}
