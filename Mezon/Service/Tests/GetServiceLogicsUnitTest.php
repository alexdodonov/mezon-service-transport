<?php
namespace Mezon\Service\Tests;

use PHPUnit\Framework\TestCase;

/**
 *
 * @author Dodonov A.A.
 * @psalm-suppress PropertyNotSetInConstructor
 */
class GetServiceLogicsUnitTest extends TestCase
{

    /**
     * Testing method getLogics
     */
    public function testGetLogis(): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport();
        $serviceTransport->setServiceLogic($fakeLogic = new FakeServiceLogic());

        // test body
        $logics = $serviceTransport->getServiceLogics();

        // assertions
        $this->assertCount(1, $logics);
        $this->assertEquals($fakeLogic, $logics[0]);
    }
}
