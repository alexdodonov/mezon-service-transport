<?php
namespace Mezon\Service\Tests;

use Mezon\Service\ServiceLogic;
use Mezon\Security\MockProvider;
use Mezon\Service\ServiceModel;

/**
 * Fake service logic.
 *
 * @author Dodonov A.A.
 */
class FakeServiceLogic extends ServiceLogic
{

    /**
     * Some fake transport
     *
     * @var ConcreteServiceTransport
     */
    var $transport;

    public function __construct()
    {
        $this->transport = new ConcreteServiceTransport(new MockProvider());

        parent::__construct($this->transport->createFetcher(), new MockProvider(), new ServiceModel());
    }

    public function test(): string
    {
        return 'test';
    }
}
