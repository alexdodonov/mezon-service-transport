<?php
namespace Mezon\Service\Tests;

use Mezon\Security\MockProvider;
use PHPUnit\Framework\TestCase;

/**
 *
 * @author Dodonov A.A.
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CallLogitExceptionUnitTest extends TestCase
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
     * Method builds service transport
     *
     * @return object service transport object
     */
    private function buildServiceTransport(): object
    {
        $serviceTransport = $this->getMockBuilder(ConcreteServiceTransport::class)
            ->setConstructorArgs([
            new MockProvider()
        ])
            ->onlyMethods([
            'createSession'
        ])
            ->getMock();
        $serviceTransport->method('createSession')->will($this->throwException(new \Exception()));

        return $serviceTransport;
    }

    /**
     * Asserting result
     *
     * @param array $result
     *            result ot be asserted
     */
    private function assertResult(array $result): void
    {
        $this->assertTrue(isset($result['message']));
        $this->assertTrue(isset($result['code']));
    }

    /**
     * Testing exception handling for callLogic
     */
    public function testExceptionHandleForCallLogic(): void
    {
        // setup
        $serviceTransport = $this->buildServiceTransport();

        // test body
        $result = $serviceTransport->callLogic(new FakeServiceLogic($serviceTransport->getRouter()), 'some-method');

        // assertions
        $this->assertResult($result);
    }

    /**
     * Testing exception handling for callPublicLogic
     */
    public function testExceptionHandleForCallPublicLogic(): void
    {
        // setup
        $serviceTransport = $this->buildServiceTransport();

        // test body
        $result = $serviceTransport->callPublicLogic(new FakeServiceLogic($serviceTransport->getRouter()), 'some-method');

        // assertions
        $this->assertResult($result);
    }
}
