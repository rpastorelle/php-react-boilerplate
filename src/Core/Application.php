<?php
namespace Core;

use Dotenv\Dotenv;
use Interop\Container\ContainerInterface;
use Slim\App;

/**
 * Core Application
 * @property-read \Slim\Views\Twig view
 */
class Application extends App {

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
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $container['app'] = function () {
            return $this;
        };

        parent::__construct($container);


        if ($this->getSetting('env') === 'dev') {
            (new Dotenv($this->basePath))->load();
        }

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
        $settings = $this->getContainer()->get('settings');

        if (! isset($settings[$key])) {
            return $default;
        }

        return $settings[$key];
    }

    private function loadRoutes()
    {
        $routes = $this->loadConfigFile('routes');
        if (! $routes) {
            throw new \RuntimeException('Missing routes file.');
        }
    }

    private function loadConfigFile($file)
    {
        $file = $this->getSetting('base_path').'/config/'.$file.'.php';
        return is_file($file) ? require($file) : false;
    }
}
