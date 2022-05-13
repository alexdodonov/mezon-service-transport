<?php
namespace Mezon\Service\Tests;

use Mezon\Router\Router;
use PHPUnit\Framework\TestCase;

/**
 *
 * @author Dodonov A.A.
 * @psalm-suppress PropertyNotSetInConstructor
 */
class TransportUnitTest extends TestCase
{

    /**
     *
     * {@inheritdoc}
     * @see TestCase::setUp()
     */
    protected function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    /**
     * Testing constructor.
     */
    public function testConstructor(): void
    {
        $serviceTransport = new ConcreteServiceTransport();

        $this->assertInstanceOf(Router::class, $serviceTransport->getRouter(), 'Router was not created');
    }

    /**
     * Testing simple calling of the logic's method.
     */
    public function testGetServiceLogic(): void
    {
        $serviceTransport = new ConcreteServiceTransport();
        $serviceTransport->setServiceLogic(new FakeServiceLogic());
        $serviceTransport->addRoute('test', 'test', 'GET');

        $result = $serviceTransport->getRouter()->callRoute('test');

        $this->assertEquals('test', $result, 'Invalid route execution result');
    }

    /**
     * Testing calling of the logic's method from array.
     */
    public function testGetServiceLogicWithUnexistingMethod(): void
    {
        $serviceTransport = new ConcreteServiceTransport();
        $serviceTransport->setServiceLogic(new FakeServiceLogic());

        $this->expectException(\Exception::class);
        $serviceTransport->addRoute('unexisting', 'unexisting', 'GET');
    }

    /**
     * Testing fetchActions method
     */
    public function testFetchActions(): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport();
        $serviceTransport->setServiceLogic(new FakeServiceLogic());

        // test body
        $serviceTransport->fetchActions(new FakeService());

        // assertions
        $this->assertTrue($serviceTransport->routeExists('/hello-world/'));
    }

    /**
     * Testing 'getParam' method
     */
    public function testGetParam(): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport();

        // test body and assertions
        $this->assertEquals(1, $serviceTransport->getParam('param'));
    }

    /**
     * Testing exception handling for unexisting route
     */
    public function testUnexistingRoute(): void
    {
        // setup and assertions
        $serviceTransport = $this->getMockBuilder(ConcreteServiceTransport::class)
            ->onlyMethods([
            'handleException'
        ])
            ->getMock();
        $serviceTransport->expects($this->once())
            ->method('handleException');

        // test body
        ob_start();
        $serviceTransport->getRouter()->callRoute('/unexisting/');
        ob_end_clean();
    }
}
