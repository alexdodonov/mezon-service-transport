<?php
namespace Mezon\Service\Tests;

use Mezon\Security\MockProvider;
use PHPUnit\Framework\TestCase;

/**
 *
 * @author Dodonov A.A.
 * @psalm-suppress PropertyNotSetInConstructor
 */
class LoadRoutesUnitTest extends TestCase
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
     * Testing loadRoutes method
     */
    public function testLoadRoutes(): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport(new MockProvider());
        $serviceTransport->setServiceLogic(new FakeServiceLogic($serviceTransport->getRouter()));

        // test body
        $serviceTransport->loadRoutes([
            [
                'route' => '/route/',
                'callback' => 'test'
            ]
        ]);

        // assertions
        $this->assertTrue($serviceTransport->routeExists('/route/'));
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
