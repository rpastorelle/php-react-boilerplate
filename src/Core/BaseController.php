<?php
namespace Core;

/**
 * Class BaseController
 * @property Application    app
 * @property array          envData
 * @property array          metadata
 * @package Core
 */
class BaseController {

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
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->metadata = [
            'title' => 'Home',
            'description' => 'Homepage description',
            'keywords' => '',
        ];

        $this->envData = [
            'ga_tracking_id' => $this->app->config('ga.tracking_id'),
            'build_path' => rtrim('build/', '/'),
        ];
    }

    /**
     * @param $metadata
     */
    protected function setMetadata($metadata)
    {
        $this->metadata = array_merge($this->metadata, $metadata);
    }

    /**
     * @return null
     */
    protected function getCurrentUser()
    {
        return $this->app->getCurrentUser();
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