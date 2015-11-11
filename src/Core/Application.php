<?php
namespace Core;

use Core\Analytics\Google;
use Dotenv\Dotenv;
use GuzzleHttp\Client;
use Slim\Route;
use Slim\Slim;

/**
 * Core Application
 * @property \GuzzleHttp\Client          apiClient
 * @property \Core\Analytics\Google      ga
 * @property array                       settings
 * @property \Slim\View                  view
 * @package Core
 */
class Application extends Slim {

    /**
     * The basePath to use for the application
     * @var string
     */
    public $basePath;

    /**
     * The GuzzleHttp Client used for API requests
     * @var \GuzzleHttp\Client
     */
    public $apiClient;

    /**
     * The Google Analytics interface
     * @var \Core\Analytics\Google
     */
    public $ga;

    /**
     * @param $basePath
     */
    public function __construct($basePath)
    {
        $this->basePath = rtrim($basePath, '/');

        if (! getenv('ENV') || getenv('ENV') === 'dev') {
            $dotenv = new Dotenv($this->basePath);
            $dotenv->load();
        }

        $config = $this->loadConfigFile('config') ?: [];
        parent::__construct($config);

        $this->loadRoutes();
        $this->setupServices();
        //$this->setupErrorHandler();
        //$this->setupNotFound();
    }

    public function isProd()
    {
        return ($this->config('ENV') === 'prod');
    }

    public function getCurrentUser()
    {
        return null;
    }

    /**
     * Override the default behavior to use our own callable parsing.
     * @author @dhrrgn
     * @param $args
     * @return Route
     */
    protected function mapRoute($args)
    {
        $pattern  = array_shift($args);
        $callable = array_pop($args);
        $callable = $this->getRouteClosure($callable);
        if (substr($pattern, -1) !== '/') {
            $pattern .= '/';
        }
        $route = new Route($pattern, $callable, $this->settings['routes.case_sensitive']);
        $this->router->map($route);
        if (count($args) > 0) {
            $route->setMiddleware($args);
        }
        return $route;
    }

    private function loadRoutes()
    {
        $routes = $this->loadConfigFile('routes');
        if (! $routes) {
            throw new \RuntimeException('Missing routes file.');
        }
    }

    private function setupServices()
    {
        $this->apiClient = new Client([
            'base_uri' => $this->config('api.base'),
            'http_errors' => false,
            'headers' => [
                'Api-Key' => $this->config('api.key'),
            ],
        ]);

        $this->ga = new Google($this->config('ga.tracking_id'), http_host());
    }

    private function loadConfigFile($file)
    {
        $file = $this->basePath.'/config/'.$file.'.php';
        return is_file($file) ? require($file) : false;
    }

    /**
     * Generates a closure for the given definition.
     * @param $callable
     * @return callable
     */
    private function getRouteClosure($callable)
    {
        if (! is_string($callable)) {
            return $callable;
        }
        list($controller, $method) = $this->parseRouteCallable($callable);
        return function () use ($controller, $method) {
            $class = $this->settings['routes.controller_namespace'].$controller;
            $refClass  = new \ReflectionClass($class);
            $refMethod = $refClass->getMethod($method);
            return $refMethod->invokeArgs($refClass->newInstance($this), func_get_args());
        };
    }

    /**
     * Parses the route definition string (i.e. 'HomeController:index')
     * @param $callable
     * @return array
     */
    private function parseRouteCallable($callable)
    {
        return explode(':', $callable);
    }
}
