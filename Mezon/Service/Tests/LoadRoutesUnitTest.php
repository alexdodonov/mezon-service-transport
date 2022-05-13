<?php
namespace Mezon\Service\Tests;

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
        $serviceTransport = new ConcreteServiceTransport();
        $serviceTransport->setServiceLogic(new FakeServiceLogic());

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
}
