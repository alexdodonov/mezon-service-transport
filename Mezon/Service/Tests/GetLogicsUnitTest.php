<?php
namespace Mezon\Service\Tests;

use Mezon\Security\MockProvider;
use PHPUnit\Framework\TestCase;

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
        $serviceTransport->setServiceLogic($fakeLogic = new FakeServiceLogic());

        // test body
        $logics = $serviceTransport->getServiceLogics();

        // assertions
        $this->assertCount(1, $logics);
        $this->assertEquals($fakeLogic, $logics[0]);
    }
}
