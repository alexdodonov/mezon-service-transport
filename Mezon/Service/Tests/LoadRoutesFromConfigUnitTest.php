<?php
namespace Mezon\Service\Tests;

use PHPUnit\Framework\TestCase;

/**
 *
 * @author Dodonov A.A.
 * @psalm-suppress PropertyNotSetInConstructor
 */
class LoadRoutesFromConfigUnitTest extends TestCase
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
     * Testing exception throwing while routes loading
     */
    public function testExceptionWhileRoutesLoading(): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport();

        // assertions
        $this->expectException(\Exception::class);

        // test body
        $serviceTransport->loadRoutesFromConfig('path-to-unexisting-file');
    }

    /**
     * Testing method loadRoatesFromConfig
     */
    public function testLoadRoatesFromConfig(): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport();
        $serviceTransport->setServiceLogic(new FakeServiceLogic());

        // test body
        $serviceTransport->loadRoutesFromConfig(__DIR__ . '/Conf/SomeRoutes.php');

        // assertions
        $this->assertTrue($serviceTransport->routeExists('/test-route/'));
    }
}
