<?php
namespace Mezon\Service\Tests;

use Mezon\Router\Router;
use Mezon\Service\ServiceLogic;
use Mezon\Transport\Tests\MockParamsFetcher;
use Mezon\Security\MockProvider;
use Mezon\Service\ServiceModel;
use PHPUnit\Framework\TestCase;
use Mezon\Security\AuthenticationProviderInterface;
use Mezon\Security\AuthorizationProviderInterface;
use Mezon\Security\ProviderInterface;

/**
 *
 * @author Dodonov A.A.
 * @psalm-suppress PropertyNotSetInConstructor
 */
class GetXProviderUnitTest extends TestCase
{

    /**
     * Testing method getAuthenticationProvider
     */
    public function testGetAuthenticationProvider(): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport(new MockProvider());

        // test body
        $provider = $serviceTransport->getAuthenticationProvider();

        // assertions
        $this->assertInstanceOf(AuthorizationProviderInterface::class, $provider);
    }

    /**
     * Testing method getAuthorizationProvider
     */
    public function testGetAuthorizationProvider(): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport(new MockProvider());

        // test body
        $provider = $serviceTransport->getAuthorizationProvider();

        // assertions
        $this->assertInstanceOf(AuthorizationProviderInterface::class, $provider);
    }

    /**
     * Testing exception
     */
    public function testGetAuthenticationProviderException(): void
    {
        // assertions
        $this->expectException(\Exception::class);

        // setup
        $serviceTransport = new ConcreteServiceTransport(new FakeProvider());

        // test body
        $serviceTransport->getAuthenticationProvider();
    }

    /**
     * Testing exception
     */
    public function testGetAuthorizationProviderException(): void
    {
        // assertions
        $this->expectException(\Exception::class);

        // setup
        $serviceTransport = new ConcreteServiceTransport(new FakeProvider());

        // test body
        $serviceTransport->getAuthorizationProvider();
    }

    /**
     * Testing method getSecurityProvider
     */
    public function testGetSecurityProvider(): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport(new MockProvider());

        // test body
        $provider = $serviceTransport->getSecurityProvider();

        // assertions
        $this->assertInstanceOf(MockProvider::class, $provider);
    }
}
