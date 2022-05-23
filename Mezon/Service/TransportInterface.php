<?php
namespace Mezon\Service;

/**
 * Interface ServiceTransportInterface
 *
 * @package Service
 * @subpackage ServiceTransportInterface
 * @author Dodonov A.A.
 * @version v.1.0 (2019/12/11)
 * @copyright Copyright (c) 2019, http://aeon.su
 */

/**
 * Interface for all transports
 *
 * @author Dodonov A.A.
 */
interface TransportInterface
{

    /**
     * Method adds's route
     *
     * @param string $route
     *            route
     * @param string $callback
     *            logic method to be called
     * @param string|array $requestMethod
     *            HTTP request method
     * @param string $callType
     *            type of the call
     */
    public function addRoute(string $route, string $callback, $requestMethod, string $callType = 'callLogic'): void;

    /**
     * Method loads routes
     *
     * @param array $routes
     *            route descriptions
     */
    public function loadRoutes(array $routes): void;

    /**
     * Method loads routes from config file
     *
     * @param string $path
     *            path to the routes description
     */
    public function loadRoutesFromConfig(string $path = './conf/routes.php'): void;

    /**
     * Method runs router
     */
    public function run(): void;

    /**
     * Method processes exception
     *
     * @param \Exception $e
     *            Exception object
     */
    public function handleException($e): void;

    /**
     * Method fetches actions for routes
     *
     * @param ServiceActionsInterface $actionsSource
     *            source of actions
     */
    public function fetchActions(ServiceActionsInterface $actionsSource): void;

    /**
     * Method returns list of user logics
     *
     * @return ServiceBaseLogic[]
     */
    public function getServiceLogics(): array;
}
