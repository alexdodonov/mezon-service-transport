<?php
namespace Mezon\Service\Tests;

use Mezon\Service\ServiceLogic;
use Mezon\Transport\Tests\MockParamsFetcher;
use Mezon\Security\MockProvider;
use Mezon\Service\ServiceModel;
use PHPUnit\Framework\TestCase;

/**
 *
 * @author Dodonov A.A.
 * @psalm-suppress PropertyNotSetInConstructor
 */
class LoadRouteUnitTest extends TestCase
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
     * Testing 'load_route' method
     */
    public function testLoadRoute(): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport(new MockProvider());
        $serviceTransport->setServiceLogic(new FakeServiceLogic());

        // test body
        $serviceTransport->loadRoute([
            'route' => '/route/',
            'callback' => 'test'
        ]);

        // assertions
        $this->assertTrue($serviceTransport->routeExists('/route/'));
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
     * Testing 'loadRoute' method with unexisting logic
     *
     * @dataProvider dataProviderForTestInvalidLoadRoute
     */
    public function testInvalidLoadRoute(array $route): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport(new MockProvider());
        $serviceTransport->setServiceLogic(
            new ServiceLogic(new MockParamsFetcher(), new MockProvider(), new ServiceModel()));

        // test body
        $this->expectException(\Exception::class);
        $serviceTransport->loadRoute($route);
    }
}
