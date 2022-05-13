<?php
namespace Mezon\Service\Tests;

use PHPUnit\Framework\TestCase;

/**
 *
 * @author Dodonov A.A.
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CallLogiсExceptionUnitTest extends TestCase
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
        $serviceTransport = new ConcreteServiceTransport();

        // test body
        $result = $serviceTransport->callLogic(new FakeServiceLogic(), 'some-method');

        // assertions
        $this->assertResult($result);
    }
}
