<?php
namespace Mezon\Service\Tests;

use Mezon\Router\Router;
use Mezon\Service\ServiceLogic;
use Mezon\Transport\Tests\MockParamsFetcher;
use Mezon\Security\MockProvider;
use Mezon\Service\ServiceModel;
use PHPUnit\Framework\TestCase;
use Mezon\Service\ServiceBaseLogic;

/**
 *
 * @author Dodonov A.A.
 * @psalm-suppress PropertyNotSetInConstructor
 */
class GetLogicsUnitTest extends TestCase
{

    /**
     * Testing method
     */
    public function testGetLogics(): void
    {
        // setup
        $serviceTransport = new ConcreteServiceTransport(new MockProvider());

        $serviceLogic = new ServiceBaseLogic(
            $serviceTransport->getParamsFetcher(),
            $serviceTransport->getSecurityProvider());

        $serviceTransport->setServiceLogic($serviceLogic);

        // test body
        $fetchedLogic = $serviceTransport->getServiceLogics()[0];

        // assertions
        $this->assertEquals($serviceLogic, $fetchedLogic);
    }
}
