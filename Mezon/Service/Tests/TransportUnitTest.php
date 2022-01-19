<?php
namespace Mezon\Service\Tests;

// TODO remove this shit
define('MEZON_DEBUG', true);

use Mezon\Router\Router;
use Mezon\Security\MockProvider;
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
        $serviceTransport = new ConcreteServiceTransport(new MockProvider());

        $this->assertInstanceOf(Router::class, $serviceTransport->getRouter(), 'Router was not created');
    }

    /**
     * Testing simple calling of the logic's method.
     */
    public function testGetServiceLogic(): void
    {
        $serviceTransport = new ConcreteServiceTransport(new MockProvider());
        $serviceTransport->setServiceLogic(new FakeServiceLogic($serviceTransport->getRouter()));
        $serviceTransport->addRoute('test', 'test', 'GET');

        $result = $serviceTransport->getRouter()->callRoute('test');

        $this->assertEquals('test', $result, 'Invalid route execution result');
    }

    /**
     * Testing simple calling of the logic's method.
     */
    public function testGetServiceLogicPublic(): void
    {
        $serviceTransport = new ConcreteServiceTransport(new MockProvider());
        $serviceTransport->setServiceLogic(new FakeServiceLogic($serviceTransport->getRouter()));
        $serviceTransport->addRoute('test', 'test', 'GET', 'public_call');

        $result = $serviceTransport->getRouter()->callRoute('test');

        $this->assertEquals('test', $result, 'Invalid public route execution result');
    }

    /**
     * Setup and run endpoint
     *
     * @param string $method
     *            method to be called
     * @return string result of the endpoint processing
     */
    protected function setupTransportWithArray(string $method): string
    {
        $serviceTransport = new ConcreteServiceTransport(new MockProvider());
        $serviceTransport->setServiceLogics([
            new FakeServiceLogic($serviceTransport->getRouter())
        ]);
        $serviceTransport->addRoute('test', $method, 'GET');

        $_GET['r'] = 'test';
        $_REQUEST['HTTP_METHOD'] = 'GET';
        ob_start();
        $serviceTransport->run();
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * Testing calling of the logic's method from array
     */
    public function testGetServiceLogicFromArray(): void
    {
        $output = $this->setupTransportWithArray('test');

        $this->assertEquals('test', $output, 'Invalid route execution result for multyple logics');
    }

    /**
     * Testing calling of the logic's method from array
     */
    public function testGetServiceLogicFromArrayException(): void
    {
        $this->expectException(\Exception::class);

        $this->setupTransportWithArray('unexisting-endpoint');
    }

    /**
     * Testing calling of the logic's method from array.
     */
    public function testGetServiceLogicWithUnexistingMethod(): void
    {
        $serviceTransport = new ConcreteServiceTransport(new MockProvider());
        $serviceTransport->setServiceLogic(new FakeServiceLogic($serviceTransport->getRouter()));

        $this->expectException(\Exception::class);
        $serviceTransport->addRoute('unexisting', 'unexisting', 'GET');
    }

    /**
     * Testing call stack formatter
     */
    public function testFormatCallStackDebug(): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport(new MockProvider());
        $exception = new \Exception('Error message', - 1);

        // test body
        $format = $serviceTransport->errorResponse($exception);

        // assertions
        $this->assertEquals(3, count($format), 'Invalid formatter');
        $this->assertTrue(isset($format['call_stack']));
    }

    /**
     * Testing call stack formatter
     */
    public function testFormatCallStackRelease(): void
    {
        // setup
        $serviceTransport = $this->getMockBuilder(ConcreteServiceTransport::class)
            ->setConstructorArgs([
            new MockProvider()
        ])
            ->onlyMethods([
            'isDebug'
        ])
            ->getMock();
        $serviceTransport->method('isDebug')->willReturn(false);
        $exception = new \Exception('Error message', - 1);

        // test body
        $format = $serviceTransport->errorResponse($exception);

        // assertions
        $this->assertFalse(isset($format['call_stack']));
    }

    /**
     * Data provider
     *
     * @return string[][][] Data set
     */
    public function dataProviderForTestInvalidLoadRoute()
    {
        return [
            [
                [
                    'route' => '/route/',
                    'callback' => 'test'
                ]
            ],
            [
                [
                    'route' => '/route/'
                ]
            ],
            [
                [
                    'callback' => 'test'
                ]
            ]
        ];
    }

    /**
     * Testing fetchActions method
     */
    public function testFetchActions(): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport(new MockProvider());
        $serviceTransport->setServiceLogic(new FakeServiceLogic($serviceTransport->getRouter()));

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
        $serviceTransport = new ConcreteServiceTransport(new MockProvider());

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
            ->setConstructorArgs([
            new MockProvider()
        ])
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

    /**
     * Testing exception throwing while routes loading
     */
    public function testExceptionWhileRoutesLoading(): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport(new MockProvider());

        // assertions
        $this->expectException(\Exception::class);

        // test body
        $serviceTransport->loadRoutesFromConfig('path-to-unexisting-file');
    }
}
