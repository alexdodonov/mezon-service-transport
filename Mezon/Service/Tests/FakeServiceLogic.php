<?php
namespace Mezon\Service\Tests;

use Mezon\Service\ServiceLogic;
use Mezon\Router\Router;
use Mezon\Security\MockProvider;

/**
 * Fake service logic.
 *
 * @author Dodonov A.A.
 */
class FakeServiceLogic extends ServiceLogic
{

    var $transport;

    public function __construct(Router &$router)
    {
        $this->transport = new ConcreteServiceTransport($router);

        parent::__construct($this->transport->createFetcher(), new MockProvider());
    }

    public function test()
    {
        return 'test';
    }
}
