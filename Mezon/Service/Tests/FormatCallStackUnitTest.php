<?php
namespace Mezon\Service\Tests;

use PHPUnit\Framework\TestCase;

/**
 *
 * @author Dodonov A.A.
 * @psalm-suppress PropertyNotSetInConstructor
 */
class FormatCallStackUnitTest extends TestCase
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
     * Testing call stack formatter
     */
    public function testFormatCallStack(): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport();
        $exception = new \Exception('Error message', - 1);

        // test body
        $format = $serviceTransport->errorResponse($exception);

        // assertions
        $this->assertEquals(3, count($format), 'Invalid formatter');
        $this->assertTrue(isset($format['call_stack']));
    }
}
