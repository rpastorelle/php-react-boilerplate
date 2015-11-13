<?php
namespace Core;

use Interop\Container\ContainerInterface;
use Slim\App;

/**
 * Core Application
 * @property-read string           base_path
 * @property-read \Slim\Views\Twig view
 */
class Application extends App {

    /**
     * @param ContainerInterface|array $container
     */
    public function __construct($container = [])
    {
        parent::__construct($container);
        $this->getContainer()['app'] = function () { return $this; };

        $this->loadRoutes();
    }

    /**
     * Gets a setting or returns the default.
     * @param $key
     * @param null $default
     * @return null
     */
    public function getSetting($key, $default = null)
    {
        if (! isset($this->settings[$key])) {
            return $default;
        }

        return $this->settings[$key];
    }

    /**
     * Load the routes file
     */
    private function loadRoutes()
    {
        $routes = $this->loadConfigFile('routes');
        if (! $routes) {
            throw new \RuntimeException('Missing routes file.');
        }
    }

    /**
     * Load a config file.
     * @param $file
     * @return bool|mixed
     */
    private function loadConfigFile($file)
    {
        $file = $this->getSetting('base_path').'/config/'.$file.'.php';
        return is_file($file) ? require($file) : false;
    }
}
