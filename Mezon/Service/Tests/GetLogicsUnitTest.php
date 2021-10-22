<?php
namespace Mezon\Service\Tests;

use Mezon\Router\Router;
use Mezon\Service\ServiceLogic;
use Mezon\Transport\Tests\MockParamsFetcher;
use Mezon\Security\MockProvider;
use Mezon\Service\ServiceModel;
use PHPUnit\Framework\TestCase;
use Mezon\Service\ServiceBaseLogic;

/**
 *
 * @author Dodonov A.A.
 * @psalm-suppress PropertyNotSetInConstructor
 */
class GetLogicsUnitTest extends TestCase
{

    /**
     * Testing method getLogics
     */
    public function testGetLogis(): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport(new MockProvider());
        $serviceTransport->setServiceLogic($fakeLogic = new FakeServiceLogic($serviceTransport->getRouter()));

        // test body
        $logics = $serviceTransport->getServiceLogics();

        // assertions
        $this->assertCount(1, $logics);
        $this->assertEquals($fakeLogic, $logics[0]);
    }
}
