<?php
namespace Mezon\Service\Tests;

use PHPUnit\Framework\TestCase;

/**
 *
 * @author Dodonov A.A.
 * @psalm-suppress PropertyNotSetInConstructor
 */
class AddRouteUnitTest extends TestCase
{

    /**
     *
     * {@inheritdoc}
     * @see TestCase::setUp()
     */
    protected function setUp(): void
    {
        $_GET['r'] = 'test';
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    /**
     * Testing addRoute method with public route
     */
    public function testAddPublicRoute(): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport();
        $serviceTransport->setServiceLogic(new FakeServiceLogic());

        // test body
        $serviceTransport->addRoute('/test/', 'publicLogic', 'GET', 'public_call');
        ob_start();
        $serviceTransport->run();
        $output = ob_get_contents();
        ob_end_clean();

        // assertions
        $this->assertEquals('public', $output);
    }

    /**
     * Testing addRoute method with secure route
     */
    public function testAddSecureRoute(): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport();
        $serviceTransport->setServiceLogic(new FakeServiceLogic());

        // test body
        $serviceTransport->addRoute('/test/', 'secureLogic', 'GET', 'secure_call');
        ob_start();
        $serviceTransport->run();
        $output = ob_get_contents();
        ob_end_clean();

        // assertions
        $this->assertEquals('secure', $output);
    }
}
