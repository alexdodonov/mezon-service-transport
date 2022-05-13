<?php
namespace Mezon\Service\Tests;

use Mezon\Service\ServiceLogic;
use Mezon\Security\MockProvider;
use Mezon\Service\ServiceModel;
use Mezon\Transport\Tests\MockParamsFetcher;

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
        $this->transport = new ConcreteServiceTransport();

        parent::__construct(new MockParamsFetcher(), new MockProvider(), new ServiceModel());
    }

    public function test(): string
    {
        return 'test';
    }
}
