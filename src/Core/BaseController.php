<?php
namespace Core;

use Slim\Container;

/**
 * Class BaseController
 * @property Application    app
 * @property array          envData
 * @property array          metadata
 * @package Core
 */
class BaseController {

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $envData;

    /**
     * @var array
     */
    protected $metadata;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->app = $container['app'];

        $this->metadata = [
            'title' => 'Home',
            'description' => 'Homepage description',
            'keywords' => '',
        ];

        $this->envData = [
            'ga_tracking_id' => $this->app->getSetting('ga.tracking_id'),
            'js_build_path' => rtrim('build/js/', '/'),
            'css_build_path' => rtrim('build/css/', '/'),
        ];
    }

    /**
     * Generate a Response object for the given {$template}
     * @param string $template
     * @param array $data
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function view($template, $data = [])
    {
        return $this->app->view->render($this->app->response, $template, $this->getTemplateData($data));
    }

    /**
     * @param $metadata
     */
    protected function setMetadata($metadata)
    {
        $this->metadata = array_merge($this->metadata, $metadata);
    }

    /**
     * @param $data
     * @return array
     */
    protected function getTemplateData($data)
    {
        return [
            'env'  => $this->envData,
            'meta' => $this->metadata,
            'data' => $data,
        ];
    }
}
