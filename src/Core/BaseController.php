<?php
namespace Core;

class BaseController {

    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @return null
     */
    protected function getCurrentUser()
    {
        return $this->app->getCurrentUser();
    }
}