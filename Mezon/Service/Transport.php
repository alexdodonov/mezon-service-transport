<?php
namespace Mezon\Service;

use Mezon\Transport\RequestParamsInterface;
use Mezon\Security\AuthenticationProviderInterface;
use Mezon\Security\AuthorizationProviderInterface;
use Mezon\Security\ProviderInterface;
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
     * @var ServiceBaseLogicInterface[]
     */
    private $serviceLogics = [];

    /**
     * Router
     *
     * @var Router
     */
    private $router;

    /**
     * Security provider
     *
     * @var ProviderInterface
     */
    private $securityProvider;

    /**
     * Constructor
     *
     * @param ProviderInterface $securityProvider
     *            Security provider
     */
    public function __construct(ProviderInterface $securityProvider)
    {
        $this->router = new Router();

        $this->router->setNoProcessorFoundErrorHandler(
            function (string $route) {
                $exception = new \Exception('Route ' . $route . ' was not found', - 1);

                $this->handleException($exception);
            });

        $this->securityProvider = $securityProvider;
    }

    /**
     * Method searches necessary logic object
     *
     * @param string $method
     *            Necessary method
     * @return ServiceBaseLogicInterface Logic object
     */
    protected function getNecessaryLogic(string $method): ServiceBaseLogicInterface
    {
        foreach ($this->serviceLogics as $logic) {
            if (method_exists($logic, $method)) {
                return $logic;
            }
        }

        throw (new \Exception('The method "' . $method . '" was not found in the set of logic objects', - 1));
    }

    /**
     * Method creates session
     *
     * @param string $token
     *            session token
     * @param
     *            string token
     */
    public abstract function createSession(string $token): string;

    /**
     * Method adds's route
     *
     * @param string $route
     *            Route
     * @param string $callback
     *            Logic method to be called
     * @param string|array $requestMethod
     *            HTTP request method
     * @param string $callType
     *            Type of the call
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
     *            Route description
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
        $callType = isset($route['call_type']) ? $route['call_type'] : 'callLogic';

        $this->addRoute($route['route'], $route['callback'], $method, $callType);
    }

    /**
     * Method loads routes
     *
     * @param array $routes
     *            Route descriptions
     */
    public function loadRoutes(array $routes): void
    {
        foreach ($routes as $route) {
            $this->loadRoute($route);
        }
    }

    /**
     * Method loads routes from config file
     *
     * @param string $path
     *            Path to the routes description
     */
    public function loadRoutesFromConfig(string $path = './conf/routes.php'): void
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
     * @param ServiceBaseLogicInterface $serviceLogic
     *            object with all service logic
     * @param string $method
     *            Logic's method to be executed
     * @param array $params
     *            Logic's parameters
     * @return mixed Result of the called method
     */
    public function callLogic(ServiceBaseLogicInterface $serviceLogic, string $method, array $params = [])
    {
        try {
            $params['SessionId'] = $this->createSession($this->getParamsFetcher()
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
     * Method runs logic functions
     *
     * @param ServiceBaseLogicInterface $serviceLogic
     *            object with all service logic
     * @param string $method
     *            Logic's method to be executed
     * @param array $params
     *            Logic's parameters
     * @return mixed Result of the called method
     */
    public function callPublicLogic(ServiceBaseLogicInterface $serviceLogic, string $method, array $params = [])
    {
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
     * Method returns true if the debug omde is ON
     */
    protected function isDebug(): bool
    {
        // TODO try to remove this crap
        return defined('MEZON_DEBUG') && MEZON_DEBUG === true;
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

        if ($this->isDebug()) {
            $result['call_stack'] = $this->formatCallStack($e);
        }

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
     * Method runs router
     *
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
     * Method processes exception
     *
     * @param \Exception $e
     *            Exception object
     * @codeCoverageIgnore
     */
    public function handleException($e): void
    {
        $this->outputException($this->errorResponse($e));

        Layer::die();
    }

    /**
     * Method fetches actions for routes
     *
     * @param ServiceBaseLogicInterface $actionsSource
     *            Source of actions
     */
    public function fetchActions(ServiceBaseLogicInterface $actionsSource): void
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
     * @return RequestParamsInterface Request data fetcher
     */
    public function getParamsFetcher(): RequestParamsInterface
    {
        if ($this->paramsFetcher !== null) {
            return $this->paramsFetcher;
        }

        return $this->paramsFetcher = $this->createFetcher();
    }

    /**
     * Method constructs request data fetcher
     *
     * @param RequestParamsInterface $paramsFetcher
     *            Request data fetcher
     */
    public function setParamsFetcher(RequestParamsInterface $paramsFetcher): void
    {
        $this->paramsFetcher = $paramsFetcher;
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
     * @return \Mezon\Router\Router router
     */
    public function &getRouter(): \Mezon\Router\Router
    {
        return $this->router;
    }

    /**
     * Method sets service logic
     *
     * @param ServiceBaseLogicInterface $serviceLogic
     *            base logic object or array
     */
    public function setServiceLogic(ServiceBaseLogicInterface $serviceLogic): void
    {
        $this->serviceLogics = [
            $serviceLogic
        ];
    }

    /**
     * Method adds service logic
     *
     * @param ServiceBaseLogicInterface $serviceLogic
     *            base logic object or array
     */
    public function addServiceLogic(ServiceBaseLogicInterface $serviceLogic): void
    {
        $this->serviceLogics[] = $serviceLogic;
    }

    /**
     * Method sets service logic
     *
     * @param ServiceBaseLogicInterface[] $serviceLogics
     *            list of logic objects
     */
    public function setServiceLogics(array $serviceLogics): void
    {
        foreach ($serviceLogics as $serviceLogic) {
            $this->addServiceLogic($serviceLogic);
        }
    }

    /**
     * Method returns security provider
     *
     * @return ProviderInterface authentication provider
     */
    public function getSecurityProvider(): ProviderInterface
    {
        return $this->securityProvider;
    }

    /**
     * Method returns authentication security provider
     *
     * @return AuthenticationProviderInterface authentication provider
     */
    public function getAuthenticationProvider(): AuthenticationProviderInterface
    {
        if ($this->securityProvider instanceof AuthenticationProviderInterface) {
            return $this->securityProvider;
        }

        throw (new \Exception('$this->securityProvider must be instance of the AuthenticationProviderInterface', - 1));
    }

    /**
     * Method returns authorization security provider
     *
     * @return AuthorizationProviderInterface authentication provider
     */
    public function getAuthorizationProvider(): AuthorizationProviderInterface
    {
        if ($this->securityProvider instanceof AuthorizationProviderInterface) {
            return $this->securityProvider;
        }

        throw (new \Exception('$this->securityProvider must be instance of the AuthorizationProviderInterface', - 1));
    }

    /**
     * Method returns list of user logics
     *
     * @return ServiceBaseLogicInterface[]
     */
    public function getServiceLogics(): array
    {
        return $this->serviceLogics;
    }
}
