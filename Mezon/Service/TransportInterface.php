<?php
namespace Mezon\Service;

use Mezon\Transport\RequestParamsInterface;

/**
 * Interface ServiceTransportInterface
 *
 * @package Service
 * @subpackage ServiceTransportInterface
 * @author Dodonov A.A.
 * @version v.1.0 (2019/12/11)
 * @copyright Copyright (c) 2019, aeon.org
 */

/**
 * Interface for all transports
 *
 * @author Dodonov A.A.
 */
interface TransportInterface
{

    /**
     * Method creates parameters fetcher
     *
     * @return RequestParamsInterface paremeters fetcher
     */
    public function createFetcher(): RequestParamsInterface;
}
