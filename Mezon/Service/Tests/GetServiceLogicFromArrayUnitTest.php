<?php
namespace Mezon\Service\Tests;

use PHPUnit\Framework\TestCase;

/**
 *
 * @author Dodonov A.A.
 * @psalm-suppress PropertyNotSetInConstructor
 */
class GetServiceLogicFromArrayUnitTest extends TestCase
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
     * Setup and run endpoint
     *
     * @param string $method
     *            method to be called
     * @return string result of the endpoint processing
     */
    protected function setupTransportWithArray(string $method): string
    {
        $serviceTransport = new ConcreteServiceTransport();
        $serviceTransport->setServiceLogics([
            new FakeServiceLogic()
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
}
