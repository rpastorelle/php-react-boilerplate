<?php
namespace Core;

use Interop\Container\ContainerInterface;
use Slim\App;

/**
 * Core Application
 * @property-read array            envData
 * @property-read string           base_path
 * @property-read \Slim\Views\Twig view
 */
class Application extends App {

    /**
     * The environment data used for all template rendering
     * @var array
     */
    public $envData;

    /**
     * @param ContainerInterface|array $container
     */
    public function __construct($container = [])
    {
        parent::__construct($container);
        $this->getContainer()['app'] = function () { return $this; };

        $this->setupEnvData();
        $this->loadRoutes();
        $this->setupNotFound();
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

    private function setupEnvData()
    {
        $this->envData = [
            'app_name' => $this->getSetting('app.name'),
            'ga_tracking_id' => $this->getSetting('ga.tracking_id'),
            'assets' => $this->getSetting('app.assets'),
            'js_build_path' => $this->getSetting('app.paths.js'),
            'css_build_path' => $this->getSetting('app.paths.css'),
        ];
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

    private function setupNotFound()
    {
        $container = $this->getContainer();
        //Override the default Not Found Handler
        $container['notFoundHandler'] = function ($c) {
            return function ($request, $response) use ($c) {
                return $c['view']
                    ->render($c['response'], 'errors/404.html', $this->getErrorTemplateData())
                    ->withStatus(404);
            };
        };
    }

    /**
     * Gets template data for error handling pages
     * @return array template data
     */
    private function getErrorTemplateData()
    {
        return [
          'env'  => $this->envData,
          'meta' => [
              'title' => 'Squad App — Snaps for Groups of Friends',
          ],
        ];
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
