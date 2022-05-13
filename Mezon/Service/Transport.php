<?php
namespace Mezon\Service;

use Mezon\Transport\RequestParamsInterface;
use Mezon\Router\Router;
use Mezon\Router\Utils;
use Mezon\System\Layer;

/**
 * Base class for all transports
 *
 * @package Service
 * @subpackage ServiceTransport
 * @author Dodonov A.A.
 * @version v.1.0 (2019/08/17)
 * @copyright Copyright (c) 2019, aeon.org
 */

/**
 * Base class for all transports
 *
 * @author Dodonov A.A.
 */
abstract class Transport implements TransportInterface
{

    /**
     * Request params fetcher
     *
     * @var ?RequestParamsInterface
     */
    private $paramsFetcher = null;

    /**
     * Service's logic objects array
     *
     * @var ServiceBaseLogic[]
     */
    private $serviceLogics = [];

    /**
     * Router
     *
     * @var Router
     */
    private $router;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->router = new Router();

        $this->router->setNoProcessorFoundErrorHandler(
            function (string $route) {
                $exception = new \Exception('Route ' . $route . ' was not found', - 1);

                $this->handleException($exception);
            });
    }

    /**
     * Method searches necessary logic object
     *
     * @param string $method
     *            necessary method
     * @return ServiceBaseLogic logic object
     */
    protected function getNecessaryLogic(string $method): ServiceBaseLogic
    {
        foreach ($this->serviceLogics as $logic) {
            if (method_exists($logic, $method)) {
                return $logic;
            }
        }

        throw (new \Exception('The method "' . $method . '" was not found in the set of logic objects', - 1));
    }

    /**
     *
     * {@inheritdoc}
     * @see TransportInterface::addRoute()
     */
    public function addRoute(string $route, string $callback, $requestMethod, string $callType = 'callLogic'): void
    {
        $localServiceLogic = $this->getNecessaryLogic($callback);

        if ($callType == 'public_call') {
            $this->router->addRoute(
                $route,
                /**
                 * Route processing
                 *
                 * @return mixed route processing result
                 */
                function () use ($localServiceLogic, $callback) {
                    return $this->callPublicLogic($localServiceLogic, $callback, []);
                },
                $requestMethod);
        } else {
            $this->router->addRoute(
                $route,
                /**
                 * Route processing
                 *
                 * @return mixed route processing result
                 */
                function () use ($localServiceLogic, $callback) {
                    return $this->callLogic($localServiceLogic, $callback, []);
                },
                $requestMethod);
        }
    }

    /**
     * Method loads single route
     *
     * @param array $route
     *            route description
     */
    public function loadRoute(array $route): void
    {
        if (! isset($route['route'])) {
            throw (new \Exception('Field "route" must be set'));
        }
        if (! isset($route['callback'])) {
            throw (new \Exception('Field "callback" must be set'));
        }
        $method = isset($route['method']) ? $route['method'] : 'GET';

        $this->addRoute($route['route'], $route['callback'], $method);
    }

    /**
     *
     * {@inheritdoc}
     * @see TransportInterface::loadRoutes()
     */
    public function loadRoutes(array $routes): void
    {
        foreach ($routes as $route) {
            $this->loadRoute($route);
        }
    }

    /**
     *
     * {@inheritdoc}
     * @see TransportInterface::loadRoutesFromConfig()
     */
    public function loadRoutesFromConfig(string $path = './Conf/Routes.php'): void
    {
        if (file_exists($path)) {
            $routes = (include ($path));

            $this->loadRoutes($routes);
        } else {
            throw (new \Exception('Route ' . $path . ' was not found', 1));
        }
    }

    /**
     * Method runs logic functions
     *
     * @param ServiceBaseLogic $serviceLogic
     *            object with all service logic
     * @param string $method
     *            logic's method to be executed
     * @param array $params
     *            logic's parameters
     * @return mixed result of the called method
     */
    public function callPublicLogic(ServiceBaseLogic $serviceLogic, string $method, array $params = [])
    {
        // TODO do we need this method be public?
        try {
            return call_user_func_array([
                $serviceLogic,
                $method
            ], $params);
        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Method runs logic functions
     *
     * @param ServiceBaseLogic $serviceLogic
     *            object with all service logic
     * @param string $method
     *            logic's method to be executed
     * @param array $params
     *            logic's parameters
     * @return mixed Result of the called method
     */
    public function callLogic(ServiceBaseLogic $serviceLogic, string $method, array $params = [])
    {
        // TODO add getSecurityProvider() to the ServiceBaseLogicInterface and use this interface here
        // TODO do we need this method be public?
        try {
            $params['SessionId'] = $serviceLogic->getSecurityProvider()->createSession(
                $this->getParamsFetcher()
                    ->getParam('session_id'));

            return call_user_func_array([
                $serviceLogic,
                $method
            ], $params);
        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Error response compilator
     *
     * @param mixed $e
     *            Exception object
     * @return array Error data
     */
    public function errorResponse($e): array
    {
        $result = [
            'message' => $e->getMessage(),
            'code' => $e->getCode()
        ];

        $result['call_stack'] = $this->formatCallStack($e);

        return $result;
    }

    /**
     * Method returns parameter
     *
     * @param string $param
     *            Parameter name
     * @param mixed $default
     *            Default value
     * @return string Parameter value
     */
    public function getParam(string $param, $default = false)
    {
        return $this->getParamsFetcher()->getParam($param, $default);
    }

    /**
     * Formatting call stack
     *
     * @param mixed $e
     *            Exception object
     * @return array Call stack
     */
    protected function formatCallStack($e): array
    {
        $stack = $e->getTrace();

        foreach ($stack as $i => $call) {
            $stack[$i] = (@$call['file'] == '' ? 'lambda : ' : @$call['file'] . ' (' . $call['line'] . ') : ') .
                (@$call['class'] == '' ? '' : $call['class'] . '->') . $call['function'];
        }

        return $stack;
    }

    /**
     *
     * {@inheritdoc}
     * @see TransportInterface::run()
     * @codeCoverageIgnore
     */
    public function run(): void
    {
        try {
            if (isset($_GET['r']) === false) {
                throw (new \Exception('Route name was not found in $_GET[\'r\']'));
            }

            $this->callRoute();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Method calls route in transport specific way
     */
    protected function callRoute(): void
    {
        print($this->router->callRoute($_GET['r']));
    }

    /**
     * Method outputs exception data
     *
     * @param array $e
     */
    public function outputException(array $e): void
    {
        print(json_encode($e));
    }

    /**
     *
     * {@inheritdoc}
     * @see TransportInterface::handleException()
     * @codeCoverageIgnore
     */
    public function handleException($e): void
    {
        $this->outputException($this->errorResponse($e));

        Layer::die();
    }

    /**
     *
     * {@inheritdoc}
     * @see TransportInterface::fetchActions()
     */
    public function fetchActions(ServiceBaseLogic $actionsSource): void
    {
        $methods = get_class_methods($actionsSource);

        foreach ($methods as $method) {
            if (strpos($method, 'action') === 0) {
                $route = Utils::convertMethodNameToRoute($method);

                $this->router->addRoute(
                    $route,
                    /**
                     * Route processing
                     *
                     * @return mixed route processing result
                     */
                    function () use ($actionsSource, $method) {
                        return $this->callPublicLogic($actionsSource, $method, []);
                    },
                    'GET');

                $this->router->addRoute(
                    $route,
                    /**
                     * Route processing
                     *
                     * @return mixed route processing result
                     */
                    function () use ($actionsSource, $method) {
                        return $this->callPublicLogic($actionsSource, $method, []);
                    },
                    'POST');
            }
        }
    }

    /**
     * Method constructs request data fetcher
     *
     * @return RequestParamsInterface request data fetcher
     */
    public function getParamsFetcher(): RequestParamsInterface
    {
        if ($this->paramsFetcher !== null) {
            return $this->paramsFetcher;
        }

        return $this->paramsFetcher = $this->createFetcher();
    }

    /**
     * Method returns true if the router exists
     *
     * @param string $route
     *            checking route
     * @return bool true if the router exists, false otherwise
     */
    public function routeExists(string $route): bool
    {
        return $this->router->routeExists($route);
    }

    /**
     * Method returns router
     *
     * @return Router router
     */
    public function &getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Method sets service logic
     *
     * @param ServiceBaseLogic $serviceLogic
     *            base logic object or array
     */
    public function setServiceLogic(ServiceBaseLogic $serviceLogic): void
    {
        $this->serviceLogics = [
            $serviceLogic
        ];
    }

    /**
     * Method adds service logic
     *
     * @param ServiceBaseLogic $serviceLogic
     *            base logic object or array
     */
    public function addServiceLogic(ServiceBaseLogic $serviceLogic): void
    {
        $this->serviceLogics[] = $serviceLogic;
    }

    /**
     * Method sets service logic
     *
     * @param ServiceBaseLogic[] $serviceLogics
     *            list of logic objects
     */
    public function setServiceLogics(array $serviceLogics): void
    {
        foreach ($serviceLogics as $serviceLogic) {
            $this->addServiceLogic($serviceLogic);
        }
    }

    /**
     *
     * {@inheritdoc}
     * @see TransportInterface::getServiceLogics()
     */
    public function getServiceLogics(): array
    {
        return $this->serviceLogics;
    }

    /**
     * Method creates parameters fetcher
     *
     * @return RequestParamsInterface paremeters fetcher
     */
    protected abstract function createFetcher(): RequestParamsInterface;
}
